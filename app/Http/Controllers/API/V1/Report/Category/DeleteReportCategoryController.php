<?php

namespace App\Http\Controllers\API\V1\Report\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Report\Models\ReportCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class DeleteReportCategoryController extends BaseController
{
    public function __invoke(string $asset_category)
    {
        try {
            CheckRolesAction::resolve()->execute('delete-report-category');

            ReportCategory::findOrFail($asset_category)->delete();

            return $this->resolveForSuccessResponseWith('Asset category deleted');
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset category not found');
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), [], HttpStatus::BadRequest);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

