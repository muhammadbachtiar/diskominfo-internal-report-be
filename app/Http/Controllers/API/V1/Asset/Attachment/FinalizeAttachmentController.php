<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\FinalizeAttachmentAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\Asset;
use Infra\Asset\Resources\AssetAttachmentResource;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class FinalizeAttachmentController extends BaseController
{
    public function __invoke(Asset $asset, Request $req)
    {
        try {
            $data = $req->validate([
                'object_key' => 'required|string',
                'original_name' => 'required|string|max:255',
                'mime_type' => 'required|string',
                'file_size' => 'required|integer|min:1',
                'file_category' => 'nullable|string|max:100',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            $attachment = FinalizeAttachmentAction::resolve()->execute(
                $asset,
                $data['object_key'],
                $data['original_name'],
                $data['mime_type'],
                $data['file_size'],
                $data['file_category'] ?? null,
                $data['tags'] ?? null
            );

            // Convert entity to model for resource
            $model = \Infra\Asset\Models\AssetAttachment::find($attachment->id);

            return $this->resolveForSuccessResponseWith(
                'Attachment uploaded successfully',
                new AssetAttachmentResource($model),
                HttpStatus::Created
            );
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}