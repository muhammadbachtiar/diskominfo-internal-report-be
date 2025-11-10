<?php

namespace Infra\Asset\Repositories;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetLoan;
use Domain\Asset\Repositories\AssetLoanRepositoryInterface;
use Infra\Asset\Models\AssetLoan as AssetLoanModel;

class EloquentAssetLoanRepository implements AssetLoanRepositoryInterface
{
    public function create(AssetLoan $loan): AssetLoan
    {
        $model = new AssetLoanModel();
        $model->id = $loan->id;
        $model->asset_id = $loan->assetId;
        $model->borrower_id = $loan->borrowerId;
        $model->loan_lat = $loan->loanLat;
        $model->loan_long = $loan->loanLong;
        $model->borrowed_at = $loan->borrowedAt->toDateTimeString();
        $model->returned_at = $loan->returnedAt?->toDateTimeString();
        $model->location_name = $loan->locationName;
        $model->pic_name = $loan->picName;
        $model->note = $loan->note;
        $model->save();

        return $this->map($model->refresh());
    }

    public function save(AssetLoan $loan): AssetLoan
    {
        $model = AssetLoanModel::query()->findOrFail($loan->id);
        $model->returned_at = $loan->returnedAt?->toDateTimeString();
        $model->save();

        return $this->map($model->refresh());
    }

    public function findOpenLoanByAsset(string $assetId): ?AssetLoan
    {
        $model = AssetLoanModel::query()
            ->where('asset_id', $assetId)
            ->whereNull('returned_at')
            ->latest('borrowed_at')
            ->first();

        return $model ? $this->map($model) : null;
    }

    public function find(string $id): ?AssetLoan
    {
        $model = AssetLoanModel::query()->find($id);
        return $model ? $this->map($model) : null;
    }

    private function map(AssetLoanModel $model): AssetLoan
    {
        return new AssetLoan(
            id: (string) $model->id,
            assetId: (string) $model->asset_id,
            borrowerId: (int) $model->borrower_id,
            loanLat: $model->loan_lat !== null ? (float) $model->loan_lat : null,
            loanLong: $model->loan_long !== null ? (float) $model->loan_long : null,
            borrowedAt: CarbonImmutable::parse($model->borrowed_at),
            returnedAt: $model->returned_at ? CarbonImmutable::parse($model->returned_at) : null,
            locationName: $model->location_name,
            picName: $model->pic_name,
            note: $model->note,
            createdAt: $model->created_at ? CarbonImmutable::parse($model->created_at) : null,
            updatedAt: $model->updated_at ? CarbonImmutable::parse($model->updated_at) : null,
        );
    }
}
