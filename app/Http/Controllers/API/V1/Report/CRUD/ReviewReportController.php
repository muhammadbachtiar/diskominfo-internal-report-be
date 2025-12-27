<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\ReviewReportAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class ReviewReportController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('reviewReport', $report);
            $data = $req->validate([
                'decision' => 'required|in:approve,reject,revision',
                'note' => 'nullable|string',
            ]);
            $res = ReviewReportAction::resolve()->execute($report, $data['decision'], $data['note'] ?? null);
            return $this->resolveForSuccessResponseWith('Review processed', $res);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
