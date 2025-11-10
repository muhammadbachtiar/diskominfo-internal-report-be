<?php

namespace App\Http\Controllers\API\V1\Roles\CRUD;

use Defuse\Crypto\Exception\BadFormatException;
use Domain\Roles\Actions\IndexRolesAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Throwable;

class IndexRoleController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = IndexRolesAction::resolve()->execute($req->query());
            if ($req->has('total_only') && $req->total_only === 'true') {
                return $this->resolveForSuccessResponseWith(
                    message: 'data roles',
                    data: $data
                );
            }

            if ($req->has('select2') && $req->select2 == true) {
                return $this->resolveForSuccessResponseWith(
                    message: 'data roles',
                    data: $data
                );
            }
            if ($req->has('total_count') && $req->total_count === 'true') {
                return $this->resolveForSuccessResponseWith(
                    message: 'data roles',
                    data: $data
                );
            }

            return $this->resolveForSuccessResponseWithPage(
                message: 'data roles',
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
