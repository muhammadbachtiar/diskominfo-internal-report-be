<?php

namespace App\Http\Controllers\API\V1\Report\Assets;

use Domain\Asset\Services\DetachAssetFromReportService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\Asset;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;

class DetachAssetFromReportController extends BaseController
{
    public function __invoke(Report $report, Asset $asset)
    {
        try {
            CheckRolesAction::resolve()->execute('detach-asset-report');

            DetachAssetFromReportService::resolve()->execute((string) $asset->id, (string) $report->id);

            return $this->resolveForSuccessResponseWith('Asset unlinked from report');
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
