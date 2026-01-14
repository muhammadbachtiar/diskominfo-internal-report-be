<?php

namespace App\Http\Controllers\API\V1\Asset\Maintenance;

use Domain\Asset\Services\MarkAssetMaintenanceService;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use InvalidArgumentException;

class CompleteAssetMaintenanceController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            CheckRolesAction::resolve()->execute('maintain-asset');

            $data = $request->validate([
                'actor_id' => ['nullable', 'integer'],
                'description' => ['nullable', 'string'],
            ]);

            $maintenance = MarkAssetMaintenanceService::resolve()->complete(
                $asset,
                $data['actor_id'] ?? null,
                $data['description'] ?? null
            );

            return $this->resolveForSuccessResponseWith('Maintenance completed', $maintenance);
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

