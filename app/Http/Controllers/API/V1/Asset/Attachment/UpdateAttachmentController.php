<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\UpdateAttachmentAction;
use Domain\Asset\DTOs\UpdateAttachmentInput;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\AssetAttachment;
use Infra\Asset\Resources\AssetAttachmentResource;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class UpdateAttachmentController extends BaseController
{
    public function __invoke(AssetAttachment $attachment, Request $req)
    {
        try {
            $data = $req->validate([
                'file_category' => 'nullable|string|max:100',
                'tags' => 'nullable|array',
                'tags.*' => 'string|max:50',
            ]);

            $input = new UpdateAttachmentInput(
                attachmentId: $attachment->id,
                fileCategory: $data['file_category'] ?? null,
                tags: $data['tags'] ?? null
            );

            $updated = UpdateAttachmentAction::resolve()->execute($input);

            // Reload model
            $model = AssetAttachment::find($updated->id);

            return $this->resolveForSuccessResponseWith(
                'Attachment updated successfully',
                new AssetAttachmentResource($model)
            );
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}