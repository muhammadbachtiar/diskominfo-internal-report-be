<?php

namespace App\Http\Controllers\API\V1\Unit\CRUD;

use Domain\Unit\Actions\CRUD\DetailUnitAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Infra\Shared\Models\Unit;

class DetailUnitController extends BaseController
{
    public function __invoke(Unit $unit)
    {
        try {
            $data = DetailUnitAction::resolve()->execute($unit);
            return $this->resolveForSuccessResponseWith('Unit Detail', $data);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), [], HttpStatus::InternalError);
        }
    }
}

