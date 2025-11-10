<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\GetReportDetailAction;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Illuminate\Http\Request;

class DetailReportController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('view', $report);
      $data = GetReportDetailAction::resolve()->execute($report, $req->query());
            return $this->resolveForSuccessResponseWith('Report', $data);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
