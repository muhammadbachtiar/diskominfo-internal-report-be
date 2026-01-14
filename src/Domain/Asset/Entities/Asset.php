<?php

namespace Domain\Asset\Entities;

use Carbon\CarbonImmutable;
use Domain\Asset\Enums\AssetStatus;

class Asset
{
    public function __construct(
        public readonly string $id,
        public string $name,
        public string $code,
        public AssetStatus $status,
        public ?string $category = null,
        public ?string $serialNumber = null,
        public ?string $categoryId = null,
        public ?string $locationId = null,
        public ?string $unitId = null,
        public ?string $purchasePrice = null,
        public ?CarbonImmutable $purchasedAt = null,
        public ?CarbonImmutable $createdAt = null,
        public ?CarbonImmutable $updatedAt = null,
        public ?CarbonImmutable $deletedAt = null,
    ) {
    }

    public function withStatus(AssetStatus $status): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            code: $this->code,
            status: $status,
            category: $this->category,
            serialNumber: $this->serialNumber,
            categoryId: $this->categoryId,
            unitId: $this->unitId,
            purchasePrice: $this->purchasePrice,
            purchasedAt: $this->purchasedAt,
            createdAt: $this->createdAt,
            updatedAt: $this->updatedAt,
            deletedAt: $this->deletedAt,
        );
    }
}
