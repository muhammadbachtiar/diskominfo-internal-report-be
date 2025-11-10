<?php

namespace App\Http\Controllers\API\V1\Roles\CRUD;

use Defuse\Crypto\Exception\BadFormatException;
use Domain\Roles\Actions\DetailRolesAction;
use Illuminate\Http\Request;
use Infra\Roles\Models\Roles;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Throwable;

class DetailRoleController extends BaseController
{
    public function __invoke(Request $req, Roles $role)
    {
        try {
            $data = DetailRolesAction::resolve()->execute($req->query(), $role);

            return $this->resolveForSuccessResponseWith(
                message: 'data details roles',
                data: $data
            );
        } catch (BadFormatException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::Forbidden
            );
        } catch (Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
