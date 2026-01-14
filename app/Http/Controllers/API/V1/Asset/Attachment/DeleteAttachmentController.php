<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\DeleteAttachmentAction;
use Infra\Asset\Models\AssetAttachment;
use Infra\Shared\Controllers\BaseController;

class DeleteAttachmentController extends BaseController
{
    public function __invoke(AssetAttachment $attachment)
    {
        try {
            DeleteAttachmentAction::resolve()->execute($attachment->id);

            return $this->resolveForSuccessResponseWith('Attachment deleted successfully');
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}