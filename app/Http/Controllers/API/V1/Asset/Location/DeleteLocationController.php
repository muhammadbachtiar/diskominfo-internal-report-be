<?php

namespace App\Http\Controllers\API\V1\Asset\Location;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Location;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DeleteLocationController extends BaseController
{
    public function __invoke(Location $location)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-locations');

            $location->delete();

            return $this->resolveForSuccessResponseWith('Location deleted');
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
