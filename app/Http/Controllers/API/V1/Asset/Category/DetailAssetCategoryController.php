<?php

namespace App\Http\Controllers\API\V1\Asset\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\AssetCategory;
use Infra\Shared\Controllers\BaseController;

class DetailAssetCategoryController extends BaseController
{
    public function __invoke( string $asset_category)
    {
        try {
            CheckRolesAction::resolve()->execute('view-asset-category');

            $data = AssetCategory::where('id', $asset_category)->firstOrFail();

            return $this->resolveForSuccessResponseWith('Asset category detail', $data);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset category not found');
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

