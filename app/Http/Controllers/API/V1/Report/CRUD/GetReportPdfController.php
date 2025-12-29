<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\GetReportPdfAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Report\Models\Report;
use Infra\Shared\Enums\HttpStatus;

class GetReportPdfController extends BaseController
{
    public function __invoke(Report $report)
    {
        try {
            $result = GetReportPdfAction::resolve()->execute($report);
            
            return response($result['content'], 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $result['filename'] . '"',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\RuntimeException $e) {
            return $this->resolveForFailedResponseWith(
                message: $e->getMessage(),
                status: HttpStatus::BadRequest
            );
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith(
                message: 'Failed to retrieve PDF: ' . $e->getMessage(),
                status: HttpStatus::InternalServerError
            );
        }
    }
}

