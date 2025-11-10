<?php

namespace Domain\Asset\Entities;

use Carbon\CarbonImmutable;
use Domain\Asset\Enums\AssetStatus;

class AssetStatusHistory
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetId,
        public readonly AssetStatus $status,
        public readonly CarbonImmutable $changedAt,
        public readonly ?int $changedBy = null,
        public readonly ?string $note = null,
    ) {
    }
}
