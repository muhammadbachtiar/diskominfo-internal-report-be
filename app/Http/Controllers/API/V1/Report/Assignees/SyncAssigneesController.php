<?php

namespace App\Http\Controllers\API\V1\Report\Assignees;

use Domain\Report\Actions\Assignees\SyncAssigneesAction;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Infra\Report\Models\Report;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class SyncAssigneesController extends BaseController
{
    public function __invoke(Report $report, Request $req)
    {
        try {
            $this->authorize('assignMemberToReport', $report);
            $data = $req->validate([
                'user_ids' => 'required|array|min:1',
                'user_ids.*' => 'integer|exists:users,id',
            ]);
            $res = SyncAssigneesAction::resolve()->execute($report, $data['user_ids']);
            return $this->resolveForSuccessResponseWith('Assignees synced', $res->assignees);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

