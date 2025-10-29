<?php

namespace App\Http\Controllers\API\V1\Asset\Status;

use Domain\Asset\Services\RetireAssetService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class RetireAssetController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            CheckRolesAction::resolve()->execute('retire-asset');

            $data = $request->validate([
                'note' => ['nullable', 'string'],
            ]);

            RetireAssetService::resolve()->execute($asset, $data['note'] ?? null);

            return $this->resolveForSuccessResponseWith('Asset retired');
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

