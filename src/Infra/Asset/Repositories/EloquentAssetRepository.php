<?php

namespace Infra\Asset\Repositories;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\Asset;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Infra\Asset\Models\Asset as AssetModel;

class EloquentAssetRepository implements AssetRepositoryInterface
{
    public function find(string $id): ?Asset
    {
        $model = AssetModel::query()->find($id);
        return $model ? $this->mapToEntity($model) : null;
    }

    public function findByCode(string $code): ?Asset
    {
        $model = AssetModel::query()->where('code', $code)->first();
        return $model ? $this->mapToEntity($model) : null;
    }

    public function save(Asset $asset): Asset
    {
        $model = AssetModel::query()->find($asset->id) ?? new AssetModel();
        $model->id = $asset->id;
        $model->name = $asset->name;
        $model->code = $asset->code;
        $model->status = $asset->status->value;
        $model->category = $asset->category;
        $model->serial_number = $asset->serialNumber;
        $model->unit_id = $asset->unitId;
        $model->purchase_price = $asset->purchasePrice;
        $model->purchased_at = $asset->purchasedAt?->toDateTimeString();
        $model->save();

        return $this->mapToEntity($model->refresh());
    }

    public function updateStatus(string $id, AssetStatus $status): void
    {
        AssetModel::query()->where('id', $id)->update(['status' => $status->value]);
    }

    public function updateLocation(string $id, ?string $locationId): void
    {
        AssetModel::query()->where('id', $id)->update(['location_id' => $locationId]);
    }

    public function delete(string $id): void
    {
        $model = AssetModel::query()->find($id);
        if ($model) {
            $model->delete();
        }
    }

    public function attachToReport(string $assetId, string $reportId, ?string $note = null): void
    {
        $asset = AssetModel::query()->findOrFail($assetId);
        $asset->reports()->syncWithoutDetaching([
            $reportId => ['note' => $note],
        ]);
    }

    public function detachFromReport(string $assetId, string $reportId): void
    {
        $asset = AssetModel::query()->findOrFail($assetId);
        $asset->reports()->detach($reportId);
    }

    private function mapToEntity(AssetModel $model): Asset
    {
        return new Asset(
            id: (string) $model->id,
            name: (string) $model->name,
            code: (string) $model->code,
            status: AssetStatus::from($model->status),
            category: $model->category,
            serialNumber: $model->serial_number,
            unitId: $model->unit_id,
            purchasePrice: $model->purchase_price !== null ? (string) $model->purchase_price : null,
            purchasedAt: $model->purchased_at ? CarbonImmutable::parse($model->purchased_at) : null,
            createdAt: $model->created_at ? CarbonImmutable::parse($model->created_at) : null,
            updatedAt: $model->updated_at ? CarbonImmutable::parse($model->updated_at) : null,
            deletedAt: $model->deleted_at ? CarbonImmutable::parse($model->deleted_at) : null,
        );
    }
}
