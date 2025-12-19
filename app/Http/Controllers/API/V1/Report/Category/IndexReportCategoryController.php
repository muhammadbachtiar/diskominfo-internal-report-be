<?php

namespace App\Http\Controllers\API\V1\Report\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\ReportCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexReportCategoryController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('view-report-categories');

            $query = ReportCategory::query();

            if ($search = $request->query('search')) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($request->query('select') === 'yes') {
                $data = $query->select('id', 'name')->limit(100)->get();
                return $this->resolveForSuccessResponseWith('Categories', $data);
            }

            $data = $query->paginate($request->query('page_size', 10));
            return $this->resolveForSuccessResponseWithPage('Categories', $data);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
