<?php

namespace Infra\Asset\Repositories;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetMaintenance;
use Domain\Asset\Repositories\AssetMaintenanceRepositoryInterface;
use Infra\Asset\Models\AssetMaintenance as AssetMaintenanceModel;

class EloquentAssetMaintenanceRepository implements AssetMaintenanceRepositoryInterface
{
    public function create(AssetMaintenance $maintenance): AssetMaintenance
    {
        $model = new AssetMaintenanceModel();
        $model->id = $maintenance->id;
        $model->asset_id = $maintenance->assetId;
        $model->description = $maintenance->description;
        $model->started_at = $maintenance->startedAt->toDateTimeString();
        $model->finished_at = $maintenance->finishedAt?->toDateTimeString();
        $model->performed_by = $maintenance->performedBy;
        $model->return_to_active_location = $maintenance->returnToActiveLocation;
        $model->save();

        return $this->map($model->refresh());
    }

    public function save(AssetMaintenance $maintenance): AssetMaintenance
    {
        $model = AssetMaintenanceModel::query()->findOrFail($maintenance->id);
        $model->description = $maintenance->description;
        $model->finished_at = $maintenance->finishedAt?->toDateTimeString();
        $model->save();

        return $this->map($model->refresh());
    }

    public function findActiveByAsset(string $assetId): ?AssetMaintenance
    {
        $model = AssetMaintenanceModel::query()
            ->where('asset_id', $assetId)
            ->whereNull('finished_at')
            ->latest('started_at')
            ->first();

        return $model ? $this->map($model) : null;
    }

    private function map(AssetMaintenanceModel $model): AssetMaintenance
    {
        return new AssetMaintenance(
            id: (string) $model->id,
            assetId: (string) $model->asset_id,
            description: (string) $model->description,
            startedAt: CarbonImmutable::parse($model->started_at),
            finishedAt: $model->finished_at ? CarbonImmutable::parse($model->finished_at) : null,
            performedBy: $model->performed_by,
            returnToActiveLocation: $model->return_to_active_location === null
                ? true
                : (bool) $model->return_to_active_location,
        );
    }
}
