<?php

namespace App\Http\Controllers\API\V1\Report\Assignees;

use Domain\Report\Actions\Assignees\RemoveAssigneeAction;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class RemoveAssigneeController extends BaseController
{
    public function __invoke(Report $report, int $user)
    {
        try {
            $this->authorize('assign', $report);
            $res = RemoveAssigneeAction::resolve()->execute($report, $user);
            return $this->resolveForSuccessResponseWith('Assignee removed', $res->assignees);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

