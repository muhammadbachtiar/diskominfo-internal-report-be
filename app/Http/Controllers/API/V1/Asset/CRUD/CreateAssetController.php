<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use Domain\Asset\Actions\CRUD\CreateAssetAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class CreateAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'code' => ['required', 'string', 'max:255'],
                'category' => ['nullable', 'string', 'max:255'],
                'serial_number' => ['nullable', 'string', 'max:255'],
                'purchase_price' => ['nullable', 'numeric'],
                'purchased_at' => ['nullable', 'date'],
                'unit_id' => ['nullable', 'uuid'],
                'category_id' => ['nullable', 'uuid'],
            ]);

            $asset = CreateAssetAction::resolve()->execute($data);

            return $this->resolveForSuccessResponseWith('Asset created', $asset, HttpStatus::Created);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), [], HttpStatus::BadRequest);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
