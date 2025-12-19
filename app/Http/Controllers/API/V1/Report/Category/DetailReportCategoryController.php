<?php

namespace App\Http\Controllers\API\V1\Report\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Report\Models\ReportCategory;
use Infra\Shared\Controllers\BaseController;

class DetailReportCategoryController extends BaseController
{
    public function __invoke( string $report_category)
    {
        try {
            CheckRolesAction::resolve()->execute('view-asset-categories');

            $data = ReportCategory::where('id', $report_category)->firstOrFail();

            return $this->resolveForSuccessResponseWith('Asset category detail', $data);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset category not found');
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

