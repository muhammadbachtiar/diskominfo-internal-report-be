<?php

namespace Domain\Asset\Actions\CRUD;

use Carbon\CarbonImmutable;
use Domain\Asset\Actions\Attachment\FinalizeAttachmentAction;
use Domain\Asset\Actions\Attachment\PresignAttachmentAction;
use Domain\Asset\Entities\Asset as AssetEntity;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Infra\Asset\Models\Asset as AssetModel;
use Infra\Shared\Foundations\Action;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class CreateAssetAction extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetStatusHistoryRepositoryInterface $history,
        protected PresignAttachmentAction $presignAttachment,
        protected FinalizeAttachmentAction $finalizeAttachment,
    ) {
    }

    public function execute(array $payload): AssetEntity
    {
        CheckRolesAction::resolve()->execute('add-asset');

        foreach (['name', 'code'] as $required) {
            if (empty(Arr::get($payload, $required))) {
                throw new InvalidArgumentException(sprintf('%s is required', $required));
            }
        }

        if ($this->assets->findByCode($payload['code'])) {
            throw new InvalidArgumentException('Asset code already exists.');
        }

        if (! empty(Arr::get($payload, 'serial_number'))) {
            $existsSerial = AssetModel::query()
                ->where('serial_number', $payload['serial_number'])
                ->exists();

            if ($existsSerial) {
                throw new InvalidArgumentException('Asset serial number already exists.');
            }
        }

        return DB::transaction(function () use ($payload) {
            $asset = new AssetEntity(
                id: (string) Uuid::uuid7(),
                name: (string) $payload['name'],
                code: (string) $payload['code'],
                status: AssetStatus::Available,
                category: Arr::get($payload, 'category'),
                serialNumber: Arr::get($payload, 'serial_number'),
                categoryId: Arr::get($payload, 'category_id'),
                unitId: Arr::get($payload, 'unit_id'),
                purchasePrice: Arr::get($payload, 'purchase_price'),
                purchasedAt: Arr::get($payload, 'purchased_at')
                    ? CarbonImmutable::parse($payload['purchased_at'])
                    : null,
            );

            $asset = $this->assets->save($asset);

            $this->history->record(new AssetStatusHistory(
                id: (string) Uuid::uuid7(),
                assetId: $asset->id,
                status: AssetStatus::Available,
                changedAt: CarbonImmutable::now(),
                changedBy: auth()->id(),
                note: 'Asset created',
            ));

            if (! empty($payload['attachments']) && is_array($payload['attachments'])) {
                $this->processAttachments($asset, $payload['attachments']);
            }

            AuditLogger::log('asset.create', 'assets', $asset->id, $payload);

            return $asset;
        });
    }

    private function processAttachments(AssetEntity $asset, array $attachments): void
    {
        // Fetch Infra Model once since we need it for actions
        $infraAsset = AssetModel::find($asset->id);
        if (! $infraAsset) {
            throw new RuntimeException('Failed to retrieve created asset for attachment processing');
        }

        foreach ($attachments as $item) {
            $file = null;
            $tags = [];

            if ($item instanceof UploadedFile) {
                $file = $item;
            } elseif (is_array($item) && isset($item['file']) && $item['file'] instanceof UploadedFile) {
                $file = $item['file'];
                $tags = $item['tags'] ?? [];
            }

            if (! $file) {
                continue;
            }

            // 1. Presign
            $presigned = $this->presignAttachment->execute(
                asset: $infraAsset,
                originalName: $file->getClientOriginalName(),
                mime: $file->getMimeType(),
                size: $file->getSize()
            );

            $uploadUrl = $presigned['url'];
            $objectKey = $presigned['object_key'];

            // 2. Upload to Presigned URL
            try {
                $response = Http::withBody(
                    file_get_contents($file->getRealPath()),
                    $file->getMimeType()
                )->put($uploadUrl);

                if (! $response->successful()) {
                    throw new RuntimeException('Failed to upload file to presigned URL: ' . $response->body());
                }
            } catch (\Exception $e) {
                throw new RuntimeException('Failed to upload attachment: ' . $e->getMessage());
            }

            // 3. Finalize
            $this->finalizeAttachment->execute(
                asset: $infraAsset,
                objectKey: $objectKey,
                originalName: $file->getClientOriginalName(),
                mimeType: $file->getMimeType(),
                fileSize: $file->getSize(),
                tags: $tags
            );
        }
    }
}
