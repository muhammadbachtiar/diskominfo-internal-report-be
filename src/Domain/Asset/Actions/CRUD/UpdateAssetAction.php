<?php

namespace Domain\Asset\Actions\CRUD;

use Carbon\CarbonImmutable;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\Asset as AssetModel;
use Infra\Shared\Foundations\Action;
use InvalidArgumentException;

class UpdateAssetAction extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
    ) {
    }

    public function execute(string $assetId, array $payload)
    {
        CheckRolesAction::resolve()->execute('edit-asset');

        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        if ($asset->status === AssetStatus::Retired) {
            throw new InvalidArgumentException('Retired assets cannot be modified.');
        }

        if (! empty($payload['code']) && $payload['code'] !== $asset->code) {
            $existingWithCode = $this->assets->findByCode($payload['code']);

            if ($existingWithCode && $existingWithCode->id !== $assetId) {
                throw new InvalidArgumentException('Asset code already exists.');
            }

            $asset->code = $payload['code'];
        }

        if (! empty($payload['serial_number'])) {
            $exists = AssetModel::query()
                ->where('serial_number', $payload['serial_number'])
                ->where('id', '!=', $assetId)
                ->exists();

            if ($exists) {
                throw new InvalidArgumentException('Asset serial number already exists.');
            }
        }

        $asset->name = $payload['name'] ?? $asset->name;
        $asset->category = $payload['category'] ?? $asset->category;
        $asset->serialNumber = $payload['serial_number'] ?? $asset->serialNumber;
        $asset->categoryId = $payload['category_id'] ?? $asset->categoryId;
        $asset->locationId = $payload['location_id'] ?? null;
        $asset->unitId = $payload['unit_id'] ?? $asset->unitId;
        $asset->purchasePrice = $payload['purchase_price'] ?? $asset->purchasePrice;
        $asset->purchasedAt = ! empty($payload['purchased_at'])
            ? CarbonImmutable::parse($payload['purchased_at'])
            : $asset->purchasedAt;

        $updated = $this->assets->save($asset);

        AuditLogger::log('asset.update', 'assets', $assetId, $payload);

        return $updated;
    }
}
