<?php

namespace App\Http\Controllers\API\V1\Report\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\ReportCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Illuminate\Support\Str;

class CreateReportCategoryController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-report-categories');

            $data = $request->validate([
                'name' => ['required', 'string'],
                'description' => ['nullable', 'string'],
            ]);

            $data['slug'] = Str::slug($data['name']);

            $category = ReportCategory::create($data);

            return $this->resolveForSuccessResponseWith('Category created', $category, HttpStatus::Created);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}