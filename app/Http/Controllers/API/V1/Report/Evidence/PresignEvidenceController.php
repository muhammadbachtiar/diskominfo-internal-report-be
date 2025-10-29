<?php

namespace App\Http\Controllers\API\V1\Report\Evidence;

use Domain\Report\Actions\PresignEvidenceAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class PresignEvidenceController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('update', $report);
            $data = $req->validate([
                'original_name' => 'nullable|string',
                'mime' => 'nullable|in:image/jpeg,image/png,image/heic,image/heif',
                'size' => 'nullable|integer|min:1',
            ]);
            $payload = PresignEvidenceAction::resolve()->execute(
                $report,
                $data['original_name'] ?? null,
                $data['mime'] ?? null,
                isset($data['size']) ? (int) $data['size'] : null,
            );
            return $this->resolveForSuccessResponseWith('Presign', $payload);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
