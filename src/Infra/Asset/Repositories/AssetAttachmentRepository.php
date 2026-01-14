<?php

namespace Infra\Asset\Repositories;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetAttachment as AssetAttachmentEntity;
use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Infra\Asset\Models\AssetAttachment;

class AssetAttachmentRepository implements AssetAttachmentRepositoryInterface
{
    public function findById(string $id): ?AssetAttachmentEntity
    {
        $model = AssetAttachment::find($id);
        
        return $model ? $this->toEntity($model) : null;
    }

    public function findByIdOrFail(string $id): AssetAttachmentEntity
    {
        $model = AssetAttachment::findOrFail($id);
        
        return $this->toEntity($model);
    }

    public function findByAssetId(string $assetId, array $filters = []): LengthAwarePaginator|iterable
    {
        $query = AssetAttachment::where('asset_id', $assetId);

        // Filter by category
        if ($category = Arr::get($filters, 'file_category')) {
            $query->where('file_category', $category);
        }

        // Filter by tags
        if ($tags = Arr::get($filters, 'tags')) {
            $tagsArray = is_string($tags) ? explode(',', $tags) : $tags;
            $query->where(function ($q) use ($tagsArray) {
                foreach ($tagsArray as $tag) {
                    $q->orWhereJsonContains('tags', trim($tag));
                }
            });
        }

        // Filter by scan status
        if ($scanStatus = Arr::get($filters, 'scan_status')) {
            $query->where('scan_status', $scanStatus);
        }

        // Order by
        $orderBy = Arr::get($filters, 'order_by', 'created_at');
        $orderDirection = Arr::get($filters, 'order_direction', 'desc');
        $query->orderBy($orderBy, $orderDirection);

        // Pagination
        if (Arr::get($filters, 'paginate', true)) {
            $perPage = max(1, (int) Arr::get($filters, 'per_page', 15));
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function create(AssetAttachmentEntity $attachment): AssetAttachmentEntity
    {
        $model = AssetAttachment::create([
            'id' => $attachment->id,
            'asset_id' => $attachment->assetId,
            'uploaded_by' => $attachment->uploadedBy,
            'original_name' => $attachment->originalName,
            'file_category' => $attachment->fileCategory,
            'mime_type' => $attachment->mimeType,
            'file_size' => $attachment->fileSize,
            'object_key' => $attachment->objectKey,
            'checksum' => $attachment->checksum,
            'storage_path' => $attachment->storagePath,
            'width' => $attachment->width,
            'height' => $attachment->height,
            'is_compressed' => $attachment->isCompressed,
            'original_size' => $attachment->originalSize,
            'is_scanned' => $attachment->isScanned,
            'scan_status' => $attachment->scanStatus,
            'scan_result' => $attachment->scanResult,
            'tags' => $attachment->tags,
        ]);

        return $this->toEntity($model);
    }

    public function update(AssetAttachmentEntity $attachment): AssetAttachmentEntity
    {
        $model = AssetAttachment::findOrFail($attachment->id);
        
        $model->update([
            'file_category' => $attachment->fileCategory,
            'tags' => $attachment->tags,
            'is_scanned' => $attachment->isScanned,
            'scan_status' => $attachment->scanStatus,
            'scan_result' => $attachment->scanResult,
            'width' => $attachment->width,
            'height' => $attachment->height,
            'is_compressed' => $attachment->isCompressed,
            'original_size' => $attachment->originalSize,
        ]);

        return $this->toEntity($model->fresh());
    }

    public function delete(string $id): bool
    {
        $model = AssetAttachment::findOrFail($id);
        
        return $model->delete();
    }

    public function exists(string $id): bool
    {
        return AssetAttachment::where('id', $id)->exists();
    }

    public function checksumExistsForAsset(string $assetId, string $checksum): bool
    {
        return AssetAttachment::where('asset_id', $assetId)
            ->where('checksum', $checksum)
            ->exists();
    }

    public function getTotalFileSizeForAsset(string $assetId): int
    {
        return (int) AssetAttachment::where('asset_id', $assetId)
            ->sum('file_size');
    }

    public function getCountForAsset(string $assetId): int
    {
        return AssetAttachment::where('asset_id', $assetId)->count();
    }

    /**
     * Convert Eloquent model to Domain entity
     */
    private function toEntity(AssetAttachment $model): AssetAttachmentEntity
    {
        return new AssetAttachmentEntity(
            id: $model->id,
            assetId: $model->asset_id,
            uploadedBy: $model->uploaded_by,
            originalName: $model->original_name,
            fileCategory: $model->file_category,
            mimeType: $model->mime_type,
            fileSize: $model->file_size,
            objectKey: $model->object_key,
            checksum: $model->checksum,
            storagePath: $model->storage_path,
            width: $model->width,
            height: $model->height,
            isCompressed: $model->is_compressed,
            originalSize: $model->original_size,
            isScanned: $model->is_scanned,
            scanStatus: $model->scan_status,
            scanResult: $model->scan_result,
            tags: $model->tags,
            createdAt: CarbonImmutable::parse($model->created_at),
            updatedAt: CarbonImmutable::parse($model->updated_at),
            deletedAt: $model->deleted_at ? CarbonImmutable::parse($model->deleted_at) : null,
        );
    }
}