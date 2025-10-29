<?php

namespace App\Http\Controllers\API\V1\User\CRUD;

use Defuse\Crypto\Exception\BadFormatException;
use Domain\User\Actions\CRUD\IndexUserAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexUserController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = IndexUserAction::resolve()->execute($req->query());

            if ($req->has('total_only') && $req->total_only === 'true') {
                return $this->resolveForSuccessResponseWith(
                    message: 'data roles',
                    data: $data
                );
            }

            if ($req->query('select') === 'yes') {
                return $this->resolveForSuccessResponseWith(
                    message: 'Get User Data',
                    data: $data
                );
            }

            return $this->resolveForSuccessResponseWithPage(
                message: 'Get User Data',
                data: $data
            );
        } catch (BadFormatException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::Forbidden
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
