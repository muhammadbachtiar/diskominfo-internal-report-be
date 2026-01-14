<?php

namespace Domain\Asset\DTOs;

class AssetAttachmentItem
{
    /**
     * @param string $assetId The asset ID to attach
     * @param string|null $note Optional note for this attachment
     */
    public function __construct(
        public readonly string $assetId,
        public readonly ?string $note = null,
    ) {
    }

    /**
     * Create from array
     *
     * @param array{asset_id: string, note?: string|null} $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            assetId: $data['asset_id'],
            note: $data['note'] ?? null
        );
    }

    /**
     * Convert to array
     *
     * @return array{asset_id: string, note: string|null}
     */
    public function toArray(): array
    {
        return [
            'asset_id' => $this->assetId,
            'note' => $this->note,
        ];
    }
}