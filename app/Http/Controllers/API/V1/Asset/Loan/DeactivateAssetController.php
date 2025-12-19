<?php

namespace App\Http\Controllers\API\V1\Asset\Loan;

use Domain\Asset\Services\ReturnAssetService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class DeactivateAssetController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            CheckRolesAction::resolve()->execute('deactivate-asset');

            $data = $request->validate([
                'actor_id' => ['nullable', 'integer'],
                'note' => ['nullable', 'string'],
            ]);

            ReturnAssetService::resolve()->execute($asset, $data['actor_id'] ?? null, $data['note'] ?? null);

            return $this->resolveForSuccessResponseWith('Asset deactivated');
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
