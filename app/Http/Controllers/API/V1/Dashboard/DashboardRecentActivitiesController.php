<?php

namespace App\Http\Controllers\API\V1\Dashboard;

use Domain\Dashboard\Actions\GetDashboardRecentActivitiesAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DashboardRecentActivitiesController extends BaseController
{
    public function __invoke()
    {
        try {
            $data = GetDashboardRecentActivitiesAction::resolve()->execute();

            return $this->resolveForSuccessResponseWith('Recent activities retrieved', $data);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith(
                'Failed to retrieve recent activities: ' . $e->getMessage(),
                [],
                HttpStatus::InternalServerError
            );
        }
    }
}
