<?php

namespace App\Http\Controllers\API\V1\Unit\CRUD;

use Domain\Unit\Actions\CRUD\UpdateUnitAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Infra\Shared\Models\Unit;

class UpdateUnitController extends BaseController
{
    public function __invoke(Request $req, Unit $unit)
    {
        try {
            $updated = UpdateUnitAction::resolve()->execute($req->all(), $unit);
            return $this->resolveForSuccessResponseWith('Update Unit Success', $updated);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), [], HttpStatus::InternalError);
        }
    }
}

