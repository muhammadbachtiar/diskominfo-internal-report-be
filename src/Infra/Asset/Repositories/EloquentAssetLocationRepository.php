<?php

namespace Infra\Asset\Repositories;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetLocation;
use Domain\Asset\Repositories\AssetLocationRepositoryInterface;
use Infra\Asset\Models\AssetLocation as AssetLocationModel;

class EloquentAssetLocationRepository implements AssetLocationRepositoryInterface
{
    public function record(AssetLocation $location): AssetLocation
    {
        $model = new AssetLocationModel();
        $model->id = $location->id;
        $model->asset_loan_id = $location->assetLoanId;
        $model->lat = $location->lat;
        $model->longitude = $location->longitude;
        $model->location_name = $location->locationName;
        $model->save();

        return $this->map($model->refresh());
    }

    private function map(AssetLocationModel $model): AssetLocation
    {
        return new AssetLocation(
            id: (string) $model->id,
            assetLoanId: (string) $model->asset_loan_id,
            lat: (float) $model->lat,
            longitude: (float) $model->longitude,
            locationName: $model->location_name,
            recordedAt: $model->created_at ? CarbonImmutable::parse($model->created_at) : null,
        );
    }
}
