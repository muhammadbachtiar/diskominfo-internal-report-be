<?php

namespace App\Http\Controllers\API\V1\Asset\Category;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Asset\Models\AssetCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class DeleteAssetCategoryController extends BaseController
{
    public function __invoke(string $asset_category)
    {
        try {
            AssetCategory::findOrFail($asset_category)->delete();

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

