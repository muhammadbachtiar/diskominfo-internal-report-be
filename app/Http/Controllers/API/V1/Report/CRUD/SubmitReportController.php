<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\SubmitReportAction;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class SubmitReportController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('submitReport', $report);
            $data = $req->validate([
                'note' => 'nullable|string',
            ]);
            $res = SubmitReportAction::resolve()->execute($report, $data['note'] ?? null);
            return $this->resolveForSuccessResponseWith('Report submitted', $res);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
