<?php

namespace Domain\Asset\DTOs;

class AttachAssetToReportInput
{
    /**
     * @param string $reportId The report ID to attach assets to
     * @param array<AssetAttachmentItem> $assets Array of assets to attach
     */
    public function __construct(
        public readonly string $reportId,
        public readonly array $assets,
    ) {
    }

    /**
     * Create from array input
     *
     * @param array{report_id: string, assets: array<array{asset_id: string, note?: string|null}>} $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        $assets = array_map(
            fn(array $item) => new AssetAttachmentItem(
                assetId: $item['asset_id'],
                note: $item['note'] ?? null
            ),
            $data['assets'] ?? []
        );

        return new self(
            reportId: $data['report_id'],
            assets: $assets,
        );
    }

    /**
     * Validate the input data
     *
     * @return array<string, string> Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        if (empty($this->reportId)) {
            $errors['report_id'] = 'Report ID is required';
        }

        if (empty($this->assets)) {
            $errors['assets'] = 'At least one asset must be provided';
        }

        if (!is_array($this->assets)) {
            $errors['assets'] = 'Assets must be an array';
        }

        foreach ($this->assets as $index => $asset) {
            if (!($asset instanceof AssetAttachmentItem)) {
                $errors["assets.{$index}"] = 'Invalid asset item format';
                continue;
            }

            if (empty($asset->assetId)) {
                $errors["assets.{$index}.asset_id"] = 'Asset ID is required';
            }
        }

        return $errors;
    }

    /**
     * Check if input is valid
     */
    public function isValid(): bool
    {
        return empty($this->validate());
    }

    /**
     * Get all asset IDs
     *
     * @return array<string>
     */
    public function getAssetIds(): array
    {
        return array_map(fn(AssetAttachmentItem $item) => $item->assetId, $this->assets);
    }

    /**
     * Get count of assets to attach
     */
    public function getAssetCount(): int
    {
        return count($this->assets);
    }
}