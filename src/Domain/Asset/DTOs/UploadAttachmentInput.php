<?php

namespace Domain\Asset\DTOs;

class UploadAttachmentInput
{
    public function __construct(
        public readonly string $assetId,
        public readonly string $originalName,
        public readonly string $mimeType,
        public readonly int $fileSize,
        public readonly ?string $fileCategory = null,
        public readonly ?array $tags = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            assetId: $data['asset_id'],
            originalName: $data['original_name'],
            mimeType: $data['mime_type'],
            fileSize: $data['file_size'],
            fileCategory: $data['file_category'] ?? null,
            tags: $data['tags'] ?? null,
        );
    }
}