<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\DownloadAttachmentAction;
use Infra\Asset\Models\AssetAttachment;
use Infra\Shared\Controllers\BaseController;

class DownloadAttachmentController extends BaseController
{
    public function __invoke(AssetAttachment $attachment)
    {
        try {
            $result = DownloadAttachmentAction::resolve()->execute($attachment->id);

            return $this->resolveForSuccessResponseWith(
                'Download URL generated successfully',
                $result
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}