<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\ListReportsAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexReportController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $data = ListReportsAction::resolve()->execute($req->query());
            if ($req->query('select') === 'yes') {
                return $this->resolveForSuccessResponseWith('Reports', $data);
            }
            return $this->resolveForSuccessResponseWithPage('Reports', $data);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
