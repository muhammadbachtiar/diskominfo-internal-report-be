<?php

namespace Domain\Asset\Repositories;

use Domain\Asset\Entities\AssetLocation;

interface AssetLocationRepositoryInterface
{
    public function record(AssetLocation $location): AssetLocation;
}
