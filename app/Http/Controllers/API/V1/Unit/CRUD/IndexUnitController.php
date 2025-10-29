<?php

namespace App\Http\Controllers\API\V1\Unit\CRUD;

use Domain\Unit\Actions\CRUD\IndexUnitAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexUnitController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = IndexUnitAction::resolve()->execute($req->query());
            if ($req->query('select') === 'yes') {
                return $this->resolveForSuccessResponseWith('Get Units', $data);
            }
            return $this->resolveForSuccessResponseWithPage('Get Units', $data);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage(), [], HttpStatus::InternalError);
        }
    }
}

