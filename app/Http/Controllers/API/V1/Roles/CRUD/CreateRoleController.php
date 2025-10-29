<?php

namespace App\Http\Controllers\API\V1\Roles\CRUD;

use Defuse\Crypto\Exception\BadFormatException;
use Domain\Roles\Actions\CreateRolesAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class CreateRoleController extends BaseController
{
    public function __invoke(Request $req)
    {
        DB::beginTransaction();
        try {
            $req->validate([
                'nama' => 'required|string',
                'permissions' => 'required|array',
            ]);
            $data = CreateRolesAction::resolve()->execute($req->all());
            DB::commit();

            return $this->resolveForSuccessResponseWith(
                message: 'Roles successful created',
                data: $data,
                status: HttpStatus::Ok,
            );

        } catch (ValidationException $th) {
            DB::rollBack();

            return $this->resolveForFailedResponseWith(
                message: 'invalid input',
                data: $th->errors(),
                status: HttpStatus::UnprocessableEntity
            );
        } catch (BadFormatException $th) {
            DB::rollBack();

            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::Forbidden
            );
        } catch (\Throwable $th) {
            DB::rollBack();

            return $this->resolveForFailedResponseWith(
                message: $th->getMessage(),
                status: HttpStatus::InternalError
            );
        }
    }
}
