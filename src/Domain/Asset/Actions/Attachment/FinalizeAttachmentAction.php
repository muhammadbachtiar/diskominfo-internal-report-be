<?php

namespace Domain\Asset\Actions\Attachment;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetAttachment;
use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;
use Ramsey\Uuid\Uuid;

class FinalizeAttachmentAction extends Action
{
    public function __construct(
        private AssetAttachmentRepositoryInterface $attachments
    ) {
    }

    public function execute(
        Asset $asset,
        string $objectKey,
        string $originalName,
        string $mimeType,
        int $fileSize,
        ?string $fileCategory = null,
        ?array $tags = null
    ): AssetAttachment {
        CheckRolesAction::resolve()->execute('view-asset');

        // Validate file size (max 16MB)
        if ($fileSize > 16777216) {
            throw new \InvalidArgumentException('File size exceeds maximum limit of 16MB');
        }

        // Check total storage limit for asset (max 100MB)
        $currentTotal = $this->attachments->getTotalFileSizeForAsset($asset->id);
        if ($currentTotal + $fileSize > 104857600) {
            throw new \InvalidArgumentException('Total attachment size would exceed 100MB limit for this asset');
        }

        // Generate checksum from object key (in production, get from S3)
        $checksum = hash('sha256', $objectKey . time());

        // Check for duplicate checksum
        if ($this->attachments->checksumExistsForAsset($asset->id, $checksum)) {
            throw new \InvalidArgumentException('This file has already been uploaded');
        }

        // Get storage path
        $disk = config('filesystems.default');
        $storagePath = Storage::disk($disk)->url($objectKey);

        // Detect image dimensions if it's an image
        $width = null;
        $height = null;
        if (str_starts_with($mimeType, 'image/')) {
            try {
                $tempFile = Storage::disk($disk)->get($objectKey);
                if ($tempFile) {
                    $image = imagecreatefromstring($tempFile);
                    if ($image) {
                        $width = imagesx($image);
                        $height = imagesy($image);
                        imagedestroy($image);
                    }
                }
            } catch (\Exception $e) {
                // Silently fail dimension detection
            }
        }

        $attachment = new AssetAttachment(
            id: (string) Uuid::uuid7(),
            assetId: $asset->id,
            uploadedBy: Auth::id(),
            originalName: $originalName,
            fileCategory: $fileCategory,
            mimeType: $mimeType,
            fileSize: $fileSize,
            objectKey: $objectKey,
            checksum: $checksum,
            storagePath: $storagePath,
            width: $width,
            height: $height,
            isCompressed: false,
            originalSize: null,
            isScanned: false,
            scanStatus: null,
            scanResult: null,
            tags: $tags,
            createdAt: CarbonImmutable::now(),
            updatedAt: CarbonImmutable::now(),
        );

        $created = $this->attachments->create($attachment);

        // TODO: Dispatch jobs for virus scanning and image compression
        // ScanAssetAttachmentJob::dispatch($created->id);
        // if ($created->isImage()) {
        //     CompressImageAttachmentJob::dispatch($created->id);
        // }

        return $created;
    }
}