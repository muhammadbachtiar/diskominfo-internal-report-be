<?php

namespace App\Http\Controllers\API\V1\User\CRUD;

use Defuse\Crypto\Exception\BadFormatException;
use Domain\User\Actions\CRUD\DeleteUserAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Infra\User\Models\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class DeleteUserController extends BaseController
{
    public function __invoke(User $user)
    {
        try {
            $data = DeleteUserAction::resolve()->execute($user);

            return $this->resolveForSuccessResponseWith(
                message: 'Delete  User Data Success',
                data: $data
            );

        } catch (BadRequestException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::BadRequest
            );
        } catch (BadFormatException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::Forbidden
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
