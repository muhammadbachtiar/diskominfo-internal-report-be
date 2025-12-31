<?php

namespace Domain\Asset\Entities;

use Carbon\CarbonImmutable;

class AssetAttachment
{
    public function __construct(
        public readonly string $id,
        public readonly string $assetId,
        public readonly int $uploadedBy,
        public string $originalName,
        public ?string $fileCategory,
        public string $mimeType,
        public int $fileSize,
        public string $objectKey,
        public string $checksum,
        public ?string $storagePath,
        public ?int $width,
        public ?int $height,
        public bool $isCompressed,
        public ?int $originalSize,
        public bool $isScanned,
        public ?string $scanStatus,
        public ?array $scanResult,
        public ?array $tags,
        public readonly CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
        public ?CarbonImmutable $deletedAt = null,
    ) {
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    public function isDocument(): bool
    {
        return in_array($this->mimeType, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function isClean(): bool
    {
        return $this->isScanned && $this->scanStatus === 'clean';
    }

    public function hasThreats(): bool
    {
        return $this->isScanned && $this->scanStatus === 'infected';
    }

    public function getHumanFileSize(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function withTags(array $tags): self
    {
        $clone = clone $this;
        $clone->tags = $tags;
        $clone->updatedAt = CarbonImmutable::now();
        return $clone;
    }

    public function withCategory(?string $category): self
    {
        $clone = clone $this;
        $clone->fileCategory = $category;
        $clone->updatedAt = CarbonImmutable::now();
        return $clone;
    }

    public function withScanResult(bool $isScanned, ?string $scanStatus, ?array $scanResult): self
    {
        $clone = clone $this;
        $clone->isScanned = $isScanned;
        $clone->scanStatus = $scanStatus;
        $clone->scanResult = $scanResult;
        $clone->updatedAt = CarbonImmutable::now();
        return $clone;
    }
}