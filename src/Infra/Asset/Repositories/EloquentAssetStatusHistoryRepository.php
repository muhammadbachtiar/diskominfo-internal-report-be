<?php

namespace Infra\Asset\Repositories;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Infra\Asset\Models\AssetStatusHistory as AssetStatusHistoryModel;

class EloquentAssetStatusHistoryRepository implements AssetStatusHistoryRepositoryInterface
{
    public function record(AssetStatusHistory $history): AssetStatusHistory
    {
        $model = new AssetStatusHistoryModel();
        $model->id = $history->id;
        $model->asset_id = $history->assetId;
        $model->status_key = $history->status->value;
        $model->changed_at = $history->changedAt->toDateTimeString();
        $model->changed_by = $history->changedBy;
        $model->note = $history->note;
        $model->save();

        return $this->map($model->refresh());
    }

    private function map(AssetStatusHistoryModel $model): AssetStatusHistory
    {
        $status = $model->status_key instanceof AssetStatus
            ? $model->status_key
            : AssetStatus::from($model->status_key);

        return new AssetStatusHistory(
            id: (string) $model->id,
            assetId: (string) $model->asset_id,
            status: $status,
            changedAt: CarbonImmutable::parse($model->changed_at),
            changedBy: $model->changed_by,
            note: $model->note,
        );
    }
}
