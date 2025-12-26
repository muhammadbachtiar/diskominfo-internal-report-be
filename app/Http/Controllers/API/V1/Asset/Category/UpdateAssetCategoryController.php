<?php

namespace App\Http\Controllers\API\V1\Asset\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\AssetCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class UpdateAssetCategoryController extends BaseController
{
    public function __invoke(Request $request, string $asset_category)
    {
        try {
            CheckRolesAction::resolve()->execute('edit-asset-category');

            $data = $request->validate([
                'name'        => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);
            $category = AssetCategory::findOrFail($asset_category);

            $category->update($data);

            return $this->resolveForSuccessResponseWith('Asset category updated', $category);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('Asset not found');
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), [], HttpStatus::BadRequest);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}

