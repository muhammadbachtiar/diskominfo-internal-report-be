<?php

namespace App\Http\Controllers\API\V1\Permission\CRUD;

use Domain\Permissions\Actions\IndexPermissionAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexPermissionController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = IndexPermissionAction::resolve()->execute($req->query());
            if ($req->has('total_only') && $req->total_only === 'true') {
                return $this->resolveForSuccessResponseWith(
                    message: 'data roles',
                    data: $data
                );
            }

            return $this->resolveForSuccessResponseWithPage(
                message: 'Permission',
                data: $data
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
