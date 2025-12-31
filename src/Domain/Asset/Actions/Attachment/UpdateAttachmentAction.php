<?php

namespace Domain\Asset\Actions\Attachment;

use Domain\Asset\DTOs\UpdateAttachmentInput;
use Domain\Asset\Entities\AssetAttachment;
use Domain\Asset\Repositories\AssetAttachmentRepositoryInterface;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;

class UpdateAttachmentAction extends Action
{
    public function __construct(
        private AssetAttachmentRepositoryInterface $attachments
    ) {
    }

    public function execute(UpdateAttachmentInput $input): AssetAttachment
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $attachment = $this->attachments->findByIdOrFail($input->attachmentId);

        // Update category if provided
        if ($input->fileCategory !== null) {
            $attachment = $attachment->withCategory($input->fileCategory);
        }

        // Update tags if provided
        if ($input->tags !== null) {
            $attachment = $attachment->withTags($input->tags);
        }

        return $this->attachments->update($attachment);
    }
}