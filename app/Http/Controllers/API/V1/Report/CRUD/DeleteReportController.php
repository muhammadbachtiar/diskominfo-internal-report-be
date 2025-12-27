<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\DeleteReportAction;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class DeleteReportController extends BaseController
{
    public function __invoke(Report $report)
    {
        try {
            $this->authorize('delete', $report);
            DeleteReportAction::resolve()->execute($report);
            return $this->resolveForSuccessResponseWith('Deleted', null, HttpStatus::Ok);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

