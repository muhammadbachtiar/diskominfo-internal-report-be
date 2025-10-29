<?php

namespace App\Http\Controllers\API\V1\User\Auth;

use Domain\User\Actions\Auth\LoginAuthAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class LoginController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $req->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);
            $data = LoginAuthAction::resolve()->execute($req->all());

            return $this->resolveForSuccessResponseWith(
                message: 'Login successful',
                data: $data,
                status: HttpStatus::Ok
            );

        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith(
                message: 'Validation Error',
                data: $th->errors(),
                status: HttpStatus::UnprocessableEntity
            );
        } catch (BadRequestException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                data: [],
                status: HttpStatus::BadRequest
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                data: [],
                status: HttpStatus::InternalError
            );
        }
    }
}
