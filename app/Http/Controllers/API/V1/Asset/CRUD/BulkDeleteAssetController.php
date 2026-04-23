<?php

namespace App\Http\Controllers\API\V1\Asset\CRUD;

use Domain\Asset\Actions\CRUD\BulkDeleteAssetAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class BulkDeleteAssetController extends BaseController
{
    public function __invoke(Request $request)
    {
        try {
            $data = $request->validate([
                'ids'   => ['required', 'array', 'min:1'],
                'ids.*' => ['required', 'string', 'exists:assets,id'],
            ]);

            $result = BulkDeleteAssetAction::resolve()->execute($data['ids']);

            return $this->resolveForSuccessResponseWith('Assets deleted successfully', $result);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->resolveForFailedResponseWith(
                'Validation Error',
                $e->errors(),
                HttpStatus::UnprocessableEntity
            );
        } catch (ModelNotFoundException $e) {
            return $this->resolveForFailedResponseWith('One or more assets not found', [], HttpStatus::NotFound);
        } catch (InvalidArgumentException $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), [], HttpStatus::BadRequest);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
