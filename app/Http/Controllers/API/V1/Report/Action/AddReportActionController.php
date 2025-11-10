<?php

namespace App\Http\Controllers\API\V1\Report\Action;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Report\Models\ReportAction;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class AddReportActionController extends BaseController
{
    public function __invoke(Request $request, Report $report)
    {
        try {
            CheckRolesAction::resolve()->execute('manage-reports');

            $data = $request->validate([
                'title' => ['required', 'string'],
                'note' => ['nullable', 'string'],
            ]);

            // Get the next sequence number
            $lastSequence = $report->actions()->max('sequence') ?? 0;
            $data['sequence'] = $lastSequence + 1;

            $action = $report->actions()->create($data);

            return $this->resolveForSuccessResponseWith('Action added', $action, HttpStatus::Created);
        } catch (ValidationException $e) {
            return $this->resolveForFailedResponseWith('Validation Error', $e->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $e) {
            return $this->resolveForFailedResponseWith($e->getMessage());
        }
    }
}
