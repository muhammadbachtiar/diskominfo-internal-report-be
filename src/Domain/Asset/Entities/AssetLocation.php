<?php

namespace Domain\Asset\Entities;

use Carbon\CarbonImmutable;

class AssetLocation
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetLoanId,
        public readonly float $lat,
        public readonly float $longitude,
        public readonly ?string $locationName = null,
        public readonly ?CarbonImmutable $recordedAt = null,
    ) {
    }
}
