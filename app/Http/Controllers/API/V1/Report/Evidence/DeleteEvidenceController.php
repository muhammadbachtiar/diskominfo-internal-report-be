<?php

namespace App\Http\Controllers\API\V1\Report\Evidence;

use Domain\Report\Actions\RemoveEvidenceAction;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Evidence\Evidence;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DeleteEvidenceController extends BaseController
{
    public function __invoke(Report $report, Evidence $evidence)
    {
        try {
            $this->authorize('update', $report);
            RemoveEvidenceAction::resolve()->execute($report, $evidence);
            return $this->resolveForSuccessResponseWith('Evidence deleted', null, HttpStatus::Ok);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

