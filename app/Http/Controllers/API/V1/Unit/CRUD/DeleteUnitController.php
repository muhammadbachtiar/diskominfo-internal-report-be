<?php

namespace App\Http\Controllers\API\V1\Unit\CRUD;

use Domain\Unit\Actions\CRUD\DeleteUnitAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Infra\Shared\Models\Unit;

class DeleteUnitController extends BaseController
{
    public function __invoke(Unit $unit)
    {
        try {
            DeleteUnitAction::resolve()->execute($unit);
            return $this->resolveForSuccessResponseWith('Delete Unit Success', true);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), [], HttpStatus::InternalError);
        }
    }
}

