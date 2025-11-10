<?php

namespace App\Http\Controllers\API\V1\Report\Assets;

use Domain\Report\Actions\Assets\ListReportAssetsAction;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;

class ListReportAssetsController extends BaseController
{
    public function __invoke(Report $report)
    {
        try {
            $assets = ListReportAssetsAction::resolve()->execute($report);

            return $this->resolveForSuccessResponseWith('Report assets', $assets);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
