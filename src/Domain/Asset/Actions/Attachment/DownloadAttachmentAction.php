<?php

namespace Domain\Asset\Actions\Attachment;

use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Storage;
use Infra\Shared\Foundations\Action;

class DownloadAttachmentAction extends Action
{
    public function __construct(
        private AssetAttachmentRepositoryInterface $attachments
    ) {
    }

    public function execute(string $attachmentId): array
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $attachment = $this->attachments->findByIdOrFail($attachmentId);

        // Check if file is clean (if scanned)
        if ($attachment->isScanned && !$attachment->isClean()) {
            throw new \RuntimeException('This file cannot be downloaded due to security concerns');
        }

        $disk = config('filesystems.default');
        
        // Generate temporary signed URL (valid for 5 minutes)
        $downloadUrl = Storage::disk($disk)->temporaryUrl(
            $attachment->objectKey,
            now()->addMinutes(5)
        );

        return [
            'download_url' => $downloadUrl,
            'filename' => $attachment->originalName,
            'mime_type' => $attachment->mimeType,
            'file_size' => $attachment->fileSize,
            'expires_at' => now()->addMinutes(5)->toIso8601String(),
        ];
    }
}