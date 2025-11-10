<?php

namespace Domain\Report\Actions\Assignees;

use Illuminate\Support\Facades\DB;
use Infra\Report\Models\Report;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;

class SyncAssigneesAction extends Action
{
    public function execute(Report $report, array $userIds): Report
    {
        CheckRolesAction::resolve()->execute('manage-assignees');
        // Validate users belong to same unit as report
        $invalid = User::whereIn('id', $userIds)
            ->where(function ($q) use ($report) {
                $q->whereNull('unit_id')->orWhere('unit_id', '!=', $report->unit_id);
            })
            ->count();
        if ($invalid > 0) {
            throw new \RuntimeException('Assignees must be in the same unit');
        }
        $report->assignees()->sync($userIds);
        \Domain\Shared\Services\AuditLogger::log('report.assign.sync', 'report_assignees', $report->id, ['user_ids' => $userIds]);
        return $report->load('assignees');
    }
}
