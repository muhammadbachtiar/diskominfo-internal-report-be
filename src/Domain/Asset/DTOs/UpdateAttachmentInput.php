<?php

namespace Domain\Asset\DTOs;

class UpdateAttachmentInput
{
    public function __construct(
        public readonly string $attachmentId,
        public readonly ?string $fileCategory = null,
        public readonly ?array $tags = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            attachmentId: $data['attachment_id'],
            fileCategory: $data['file_category'] ?? null,
            tags: $data['tags'] ?? null,
        );
    }
}