<?php

namespace Domain\Asset\Repositories;

use Domain\Asset\Entities\AssetAttachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AssetAttachmentRepositoryInterface
{
    /**
     * Find attachment by ID
     */
    public function findById(string $id): ?AssetAttachment;

    /**
     * Find attachment by ID or fail
     */
    public function findByIdOrFail(string $id): AssetAttachment;

    /**
     * Get all attachments for an asset
     */
    public function findByAssetId(string $assetId, array $filters = []): LengthAwarePaginator|iterable;

    /**
     * Create a new attachment
     */
    public function create(AssetAttachment $attachment): AssetAttachment;

    /**
     * Update an attachment
     */
    public function update(AssetAttachment $attachment): AssetAttachment;

    /**
     * Delete an attachment (soft delete)
     */
    public function delete(string $id): bool;

    /**
     * Check if attachment exists
     */
    public function exists(string $id): bool;

    /**
     * Check if checksum already exists for asset
     */
    public function checksumExistsForAsset(string $assetId, string $checksum): bool;

    /**
     * Get total file size for an asset
     */
    public function getTotalFileSizeForAsset(string $assetId): int;

    /**
     * Get attachment count for an asset
     */
    public function getCountForAsset(string $assetId): int;
}