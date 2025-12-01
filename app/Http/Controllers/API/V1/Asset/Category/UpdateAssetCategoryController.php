<?php

namespace App\Http\Controllers\API\V1\Asset\Category;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Infra\Asset\Models\AssetCategory;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class UpdateAssetCategoryController extends BaseController
{
    public function __invoke(Request $request, string $id)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-asset-categories');

            $category = AssetCategory::findOrFail($id);

            $data = $request->validate([
                'name' => ['sometimes', 'required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
            ]);

            if (isset($data['name'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $category->update($data);

            return $this->resolveForSuccessResponseWith('Asset category updated successfully', $category);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), null, HttpStatus::NotFound);
        }
    }
}
