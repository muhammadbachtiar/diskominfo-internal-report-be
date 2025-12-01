<?php

namespace App\Http\Controllers\API\V1\User\Auth;

use Domain\User\Actions\Auth\GetDataAuthAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class GetDataAuthController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = GetDataAuthAction::resolve()->execute($req->query());

            return $this->resolveForSuccessResponseWith(
                message: 'Data success to data',
                data: $data,
                status: HttpStatus::Ok
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: 'Failed to get data',
                data: [$th->getMessage()],
                status: HttpStatus::InternalError
            );
        }
    }
}
