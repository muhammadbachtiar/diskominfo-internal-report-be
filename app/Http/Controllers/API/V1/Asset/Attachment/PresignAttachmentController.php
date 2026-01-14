<?php

namespace App\Http\Controllers\API\V1\Asset\Attachment;

use Domain\Asset\Actions\Attachment\PresignAttachmentAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\Asset;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class PresignAttachmentController extends BaseController
{
    public function __invoke(Asset $asset, Request $req)
    {
        try {
            $data = $req->validate([
                'original_name' => 'required|string|max:255',
                'mime_type' => 'required|string|in:image/jpeg,image/png,image/webp,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size' => 'required|integer|min:1|max:16777216', // Max 16MB
            ]);

            $payload = PresignAttachmentAction::resolve()->execute(
                $asset,
                $data['original_name'],
                $data['mime_type'],
                $data['file_size']
            );

            return $this->resolveForSuccessResponseWith('Presign URL generated successfully', $payload);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}