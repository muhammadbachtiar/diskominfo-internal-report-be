<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\DetailAttachmentAction;
use Illuminate\Http\Request;
use Infra\Asset\Models\AssetAttachment;
use Infra\Asset\Resources\AssetAttachmentResource;
use Infra\Shared\Controllers\BaseController;

class DetailAttachmentController extends BaseController
{
    public function __invoke(AssetAttachment $attachment, Request $req)
    {
        try {
            $includeDownloadUrl = $req->boolean('include_download_url', false);

            $result = DetailAttachmentAction::resolve()->execute(
                $attachment->id,
                $includeDownloadUrl
            );

            $data = new AssetAttachmentResource($attachment);
            
            if ($includeDownloadUrl && isset($result['download_url'])) {
                $data = array_merge($data->toArray($req), [
                    'download_url' => $result['download_url']
                ]);
            }

            return $this->resolveForSuccessResponseWith(
                'Attachment retrieved successfully',
                $data
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}