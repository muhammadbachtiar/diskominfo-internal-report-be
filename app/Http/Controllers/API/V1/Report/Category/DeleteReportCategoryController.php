<?php

namespace App\Http\Controllers\API\V1\Report\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Report\Models\ReportCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DeleteReportCategoryController extends BaseController
{
    public function __invoke(string $id)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-report-categories');

            $category = ReportCategory::findOrFail($id);
            $category->delete();

            return $this->resolveForSuccessResponseWith('Report category deleted successfully');
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), null, HttpStatus::NotFound);
        }
    }
}
