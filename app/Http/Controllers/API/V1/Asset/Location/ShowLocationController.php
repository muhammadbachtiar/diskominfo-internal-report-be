<?php

namespace App\Http\Controllers\API\V1\Asset\Location;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Location;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class ShowLocationController extends BaseController
{
    public function __invoke(string $id)
    {
        try {
            CheckRolesAction::resolve()->execute('view-locations');

            $location = Location::findOrFail($id);

            return $this->resolveForSuccessResponseWith('Location retrieved', $location);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage(), null, HttpStatus::NotFound);
        }
    }
}
