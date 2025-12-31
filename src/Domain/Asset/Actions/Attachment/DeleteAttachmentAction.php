<?php

namespace Domain\Asset\Actions\Attachment;

use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Storage;
use Infra\Shared\Foundations\Action;

class DeleteAttachmentAction extends Action
{
    public function __construct(
        private AssetAttachmentRepositoryInterface $attachments
    ) {
    }

    public function execute(string $attachmentId, bool $deleteFromStorage = true): bool
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $attachment = $this->attachments->findByIdOrFail($attachmentId);

        // Delete from storage if requested
        if ($deleteFromStorage) {
            try {
                $disk = config('filesystems.default');
                Storage::disk($disk)->delete($attachment->objectKey);
            } catch (\Exception $e) {
                // Log error but continue with database deletion
                \Log::warning('Failed to delete attachment from storage', [
                    'attachment_id' => $attachmentId,
                    'object_key' => $attachment->objectKey,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Soft delete from database
        return $this->attachments->delete($attachmentId);
    }
}