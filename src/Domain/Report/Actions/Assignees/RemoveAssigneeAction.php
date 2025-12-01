<?php

namespace Domain\Report\Actions\Assignees;

use Infra\Report\Models\Report;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;

class RemoveAssigneeAction extends Action
{
    public function execute(Report $report, int $userId): Report
    {
        CheckRolesAction::resolve()->execute('manage-assignees');
        $report->assignees()->detach($userId);
        \Domain\Shared\Services\AuditLogger::log('report.assign.remove', 'report_assignees', $report->id, ['user_id' => $userId]);
        return $report->load('assignees');
    }
}
