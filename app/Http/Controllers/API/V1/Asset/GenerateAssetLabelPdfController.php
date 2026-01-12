<?php

namespace App\Http\Controllers\API\V1\Asset;

use Domain\Asset\Actions\GenerateAssetLabelPdfAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class GenerateAssetLabelPdfController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'assets' => ['required', 'array', 'min:1'],
                'assets.*' => ['required', 'string', 'exists:assets,id'],
            ]);

            $result = GenerateAssetLabelPdfAction::resolve()->execute($data);

            return response($result['content'], 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->resolveForFailedResponseWith(
                message: 'Validation Error',
                data: $e->errors(),
                status: HttpStatus::UnprocessableEntity
            );
        } catch (\InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith(
                message: $e->getMessage(),
                status: HttpStatus::BadRequest
            );
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith(
                message: 'Failed to generate PDF: ' . $e->getMessage(),
                status: HttpStatus::InternalServerError
            );
        }
    }
}
