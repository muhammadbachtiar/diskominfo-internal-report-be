<?php

namespace App\Http\Controllers\API\V1\Asset\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\AssetCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DeleteAssetCategoryController extends BaseController
{
    public function __invoke(string $id)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-asset-categories');

            $category = AssetCategory::findOrFail($id);
            $category->delete();

            return $this->resolveForSuccessResponseWith('Asset category deleted successfully');
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), null, HttpStatus::NotFound);
        }
    }
}
