<?php

namespace App\Http\Controllers\API\V1\Dashboard;

use Domain\Dashboard\Actions\GetDashboardSummaryAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DashboardSummaryController extends BaseController
{
    public function __invoke()
    {
        try {
            $summary = GetDashboardSummaryAction::resolve()->execute();

            return $this->resolveForSuccessResponseWith('Dashboard summary retrieved', $summary);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith(
                'Failed to retrieve dashboard summary: ' . $e->getMessage(),
                [],
                HttpStatus::InternalServerError
            );
        }
    }
}
