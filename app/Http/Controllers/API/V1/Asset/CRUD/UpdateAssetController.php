<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use Domain\Asset\Actions\CRUD\UpdateAssetAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class UpdateAssetController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            $data = $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'category' => ['sometimes', 'nullable', 'string', 'max:255'],
                'serial_number' => ['sometimes', 'nullable', 'string', 'max:255'],
                'purchase_price' => ['sometimes', 'nullable', 'numeric'],
                'purchased_at' => ['sometimes', 'nullable', 'date'],
                'unit_id' => ['sometimes', 'nullable', 'uuid'],
            ]);

            $assetData = UpdateAssetAction::resolve()->execute($asset, $data);

            return $this->resolveForSuccessResponseWith('Asset updated', $assetData);
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

