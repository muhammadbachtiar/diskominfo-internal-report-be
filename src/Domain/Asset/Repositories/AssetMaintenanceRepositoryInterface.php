<?php

namespace Domain\Asset\Repositories;

use Domain\Asset\Entities\AssetMaintenance;

interface AssetMaintenanceRepositoryInterface
{
    public function create(AssetMaintenance $maintenance): AssetMaintenance;

    public function save(AssetMaintenance $maintenance): AssetMaintenance;

    public function findActiveByAsset(string $assetId): ?AssetMaintenance;
}
