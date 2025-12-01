<?php

namespace Domain\Asset\Repositories;

use Domain\Asset\Entities\AssetLoan;

interface AssetLoanRepositoryInterface
{
    public function create(AssetLoan $loan): AssetLoan;

    public function save(AssetLoan $loan): AssetLoan;

    public function findOpenLoanByAsset(string $assetId): ?AssetLoan;

    public function find(string $id): ?AssetLoan;
}
