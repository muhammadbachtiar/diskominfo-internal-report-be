<?php

namespace App\Http\Controllers\API\V1\Asset\Location;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Location;
use Infra\Shared\Controllers\BaseController;

class DetailLocationController extends BaseController
{
    public function __invoke(Location $location)
    {
        try {
            CheckRolesAction::resolve()->execute('view-location');

            return $this->resolveForSuccessResponseWith('location detail', $location);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
