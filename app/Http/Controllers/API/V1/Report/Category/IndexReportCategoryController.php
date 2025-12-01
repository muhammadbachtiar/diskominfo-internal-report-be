<?php

namespace App\Http\Controllers\API\V1\Report\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Infra\Report\Models\ReportCategory;
use Infra\Shared\Controllers\BaseController;

class IndexReportCategoryController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('view-report-categories');

            $query = ReportCategory::query();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Select mode - return all without pagination
            if ($request->get('select') === 'true') {
                $categories = $query->orderBy('name')->limit(100)->get();
                return $this->resolveForSuccessResponseWith('Report categories retrieved', $categories);
            }

            // Paginated mode
            $perPage = $request->get('page_size', 10);
            $categories = $query->orderBy('name')->paginate($perPage);

            return $this->resolveForSuccessResponseWith('Report categories retrieved', $categories);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
