<?php

namespace App\Http\Controllers\API\V1\User\CRUD;

use Domain\User\Actions\CRUD\UpdateUserAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Infra\User\Models\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Throwable;

class UpdateUserController extends BaseController
{
    public function __invoke(Request $req, User $user)
    {

        DB::beginTransaction();
        try {
            if ($user->id == Auth::user()->id) {
                throw new BadRequestException('user id Tidak bisa diupdate');
            }
            $data = UpdateUserAction::resolve()->execute($req->all(), $user);
            DB::commit();

            return $this->resolveForSuccessResponseWith(
                message: 'update successful',
                data: $data,
                status: HttpStatus::Ok
            );
        } catch (BadRequestException $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                data: [],
                status: HttpStatus::BadRequest
            );
        } catch (Throwable $th) {
            DB::rollBack();

            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                data: [],
                status: HttpStatus::InternalError
            );
        }
    }
}
