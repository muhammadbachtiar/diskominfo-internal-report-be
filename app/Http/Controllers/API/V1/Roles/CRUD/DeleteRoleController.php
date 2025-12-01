<?php

namespace App\Http\Controllers\API\V1\Roles\CRUD;

use Domain\Roles\Actions\DeleteRolesAction;
use Infra\Roles\Models\Roles;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DeleteRoleController extends BaseController
{
    public function __invoke(Roles $role)
    {
        try {

            DeleteRolesAction::resolve()->execute($role);

            return $this->resolveForSuccessResponseWith(
                message: 'Delete Roles Successful'
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
