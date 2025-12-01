<?php

namespace App\Http\Controllers\API\V1\Report\CRUD;

use Domain\Report\Actions\UpdateReportAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class UpdateReportController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('update', $report);
            $data = $req->validate([
                'title' => 'sometimes|string',
                'description' => 'sometimes|nullable|string',
                'category' => 'sometimes|nullable|string',
                'location' => 'sometimes|nullable|string',
                'lat' => 'sometimes|numeric|nullable',
                'lng' => 'sometimes|numeric|nullable',
                'accuracy' => 'sometimes|numeric|nullable',
                'event_at' => 'sometimes|date|nullable',
            ]);
            $res = UpdateReportAction::resolve()->execute($report, $data);
            return $this->resolveForSuccessResponseWith('Updated', $res);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

