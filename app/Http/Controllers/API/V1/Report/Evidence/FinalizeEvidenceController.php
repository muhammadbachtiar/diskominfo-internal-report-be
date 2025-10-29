<?php

namespace App\Http\Controllers\API\V1\Report\Evidence;

use Domain\Report\Actions\FinalizeEvidenceAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class FinalizeEvidenceController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('update', $report);
            $data = $req->validate([
                'object_key' => 'required|string',
                'original_name' => 'nullable|string',
                'mime' => 'nullable|string',
                'size' => 'nullable|integer|min:1',
            ]);
            $evidence = FinalizeEvidenceAction::resolve()->execute(
                $report,
                $data['object_key'],
                $data['original_name'] ?? null,
                $data['mime'] ?? null,
                isset($data['size']) ? (int) $data['size'] : null,
            );
            return $this->resolveForSuccessResponseWith('Evidence accepted', $evidence, HttpStatus::Created);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
