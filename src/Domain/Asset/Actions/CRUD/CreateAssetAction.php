<?php

namespace Domain\Asset\Actions\CRUD;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\Asset as AssetEntity;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Support\Arr;
use Infra\Asset\Models\Asset as AssetModel;
use Infra\Shared\Foundations\Action;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class CreateAssetAction extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetStatusHistoryRepositoryInterface $history,
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

        AuditLogger::log('asset.create', 'assets', $asset->id, $payload);

        return $asset;
    }
}
