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

class StartAssetMaintenanceController extends BaseController
{
    public function __invoke(Request $request, string $asset)
    {
        try {
            CheckRolesAction::resolve()->execute('maintain-asset');

            $data = $request->validate([
                'description' => ['required', 'string'],
                'performed_by' => ['nullable', 'integer'],
                'return_to_active_location' => ['nullable', 'boolean'],
            ]);

            $maintenance = MarkAssetMaintenanceService::resolve()->start(
                $asset,
                $data['description'],
                $data['performed_by'] ?? null,
                array_key_exists('return_to_active_location', $data)
                    ? (bool) $data['return_to_active_location']
                    : true,
            );

            return $this->resolveForSuccessResponseWith('Maintenance started', $maintenance);
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
