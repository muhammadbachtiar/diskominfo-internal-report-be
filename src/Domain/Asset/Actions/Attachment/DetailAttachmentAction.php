<?php

namespace Domain\Asset\Actions\Attachment;

use Domain\Asset\Entities\AssetAttachment;
use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Storage;
use Infra\Shared\Foundations\Action;

class DetailAttachmentAction extends Action
{
    public function __construct(
        private AssetAttachmentRepositoryInterface $attachments
    ) {
    }

    public function execute(string $attachmentId, bool $includeDownloadUrl = false): array
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $attachment = $this->attachments->findByIdOrFail($attachmentId);

        $result = [
            'attachment' => $attachment,
        ];

        if ($includeDownloadUrl) {
            $result['download_url'] = $this->generateDownloadUrl($attachment);
        }

        return $result;
    }

    private function generateDownloadUrl(AssetAttachment $attachment): string
    {
        $disk = config('filesystems.default');
        
        // Generate temporary signed URL (valid for 5 minutes)
        return Storage::disk($disk)->temporaryUrl(
            $attachment->objectKey,
            now()->addMinutes(5)
        );
    }
}