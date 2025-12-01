<?php

namespace Domain\Asset\Repositories;

use Domain\Asset\Entities\AssetStatusHistory;

interface AssetStatusHistoryRepositoryInterface
{
    public function record(AssetStatusHistory $history): AssetStatusHistory;
}
