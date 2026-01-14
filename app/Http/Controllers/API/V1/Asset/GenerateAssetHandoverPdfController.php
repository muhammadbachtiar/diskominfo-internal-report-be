<?php

namespace App\Http\Controllers\API\V1\Asset;

use Domain\Asset\Actions\GenerateAssetHandoverPdfAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class GenerateAssetHandoverPdfController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'date' => ['required', 'date'],
                'first_party' => ['required', 'array'],
                'first_party.name' => ['required', 'string'],
                'first_party.nip' => ['required', 'string'],
                'first_party.position' => ['required', 'string'],
                'first_party.agency' => ['required', 'string'],
                'first_party.rank' => ['required', 'string'],
                'second_party' => ['required', 'array'],
                'second_party.name' => ['required', 'string'],
                'second_party.nip' => ['required', 'string'],
                'second_party.position' => ['required', 'string'],
                'second_party.agency' => ['required', 'string'],
                'second_party.rank' => ['required', 'string'],
                'knowing' => ['required', 'array'],
                'knowing.name' => ['required', 'string'],
                'knowing.nip' => ['required', 'string'],
                'knowing.position' => ['nullable', 'string'],
                'knowing.agency' => ['nullable', 'string'],
                'knowing.rank' => ['required', 'string'],
                'assets' => ['required', 'array', 'min:1'],
                'assets.*.asset_id' => ['required', 'string', 'exists:assets,id'],
                'assets.*.description' => ['nullable', 'string'],
            ]);

            $result = GenerateAssetHandoverPdfAction::resolve()->execute($data);

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
