<?php

namespace App\Http\Controllers\API\V1\Unit\CRUD;

use Domain\Unit\Actions\CRUD\CreateUnitAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class CreateUnitController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $unit = CreateUnitAction::resolve()->execute($req->all());
            return $this->resolveForSuccessResponseWith('Create Unit Success', $unit, HttpStatus::Created);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), [], HttpStatus::InternalError);
        }
    }
}

