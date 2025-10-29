<?php

namespace Domain\Asset\Entities;

use Carbon\CarbonImmutable;

class AssetMaintenance
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetId,
        public string $description,
        public readonly CarbonImmutable $startedAt,
        public ?CarbonImmutable $finishedAt = null,
        public readonly ?int $performedBy = null,
        public readonly bool $returnToActiveLocation = true,
    ) {
    }

    public function markCompleted(CarbonImmutable $finishedAt): self
    {
        return new self(
            id: $this->id,
            assetId: $this->assetId,
            description: $this->description,
            startedAt: $this->startedAt,
            finishedAt: $finishedAt,
            performedBy: $this->performedBy,
            returnToActiveLocation: $this->returnToActiveLocation,
        );
    }
}
