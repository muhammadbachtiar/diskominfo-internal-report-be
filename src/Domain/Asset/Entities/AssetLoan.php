<?php

namespace Domain\Asset\Entities;

use Carbon\CarbonImmutable;

class AssetLoan
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetId,
        public readonly int $borrowerId,
        public readonly ?float $loanLat,
        public readonly ?float $loanLong,
        public readonly CarbonImmutable $borrowedAt,
        public readonly ?CarbonImmutable $returnedAt = null,
        public readonly ?string $locationName = null,
        public readonly ?string $picName = null,
        public readonly ?string $note = null,
        public readonly ?CarbonImmutable $createdAt = null,
        public readonly ?CarbonImmutable $updatedAt = null,
    ) {
    }

    public function markReturned(CarbonImmutable $returnedAt): self
    {
        return new self(
            id: $this->id,
            assetId: $this->assetId,
            borrowerId: $this->borrowerId,
            loanLat: $this->loanLat,
            loanLong: $this->loanLong,
            borrowedAt: $this->borrowedAt,
            returnedAt: $returnedAt,
            locationName: $this->locationName,
            picName: $this->picName,
            note: $this->note,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
        );
    }
}
