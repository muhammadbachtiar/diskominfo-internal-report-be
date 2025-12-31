<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\IndexAttachmentAction;
use Illuminate\Http\Request;
use Infra\Asset\Models\Asset;
use Infra\Asset\Resources\AssetAttachmentResource;
use Infra\Shared\Controllers\BaseController;

class IndexAttachmentController extends BaseController
{
    public function __invoke(Asset $asset, Request $req)
    {
        try {
            $filters = $req->only([
                'file_category',
                'tags',
                'scan_status',
                'order_by',
                'order_direction',
                'per_page',
                'paginate',
            ]);

            $attachments = IndexAttachmentAction::resolve()->execute($asset, $filters);

            return $this->resolveForSuccessResponseWith(
                'Attachments retrieved successfully',
                AssetAttachmentResource::collection($attachments)
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}