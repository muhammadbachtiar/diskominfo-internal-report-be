<?php

namespace App\Http\Controllers\API\V1\Report\Evidence;

use Infra\Report\Models\Evidence\Evidence;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class GetEvidenceController extends BaseController
{
    public function __invoke(Report $report, Evidence $evidence)
    {
        try {
            // Authorize viewing the report
            $this->authorize('view', $report);

            if ($evidence->report_id !== $report->id) {
                return $this->resolveForFailedResponseWith('Evidence does not belong to the report', [], HttpStatus::Forbidden);
            }

            return $this->resolveForSuccessResponseWith('Evidence', $evidence);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), [], HttpStatus::InternalError);
        }
    }
}

