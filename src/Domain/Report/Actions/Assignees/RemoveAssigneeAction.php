<?php

namespace Domain\Report\Actions\Assignees;

use Domain\Notification\Actions\SendAppNotificationAction;
use Infra\Report\Models\Report;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Foundations\Action;

class RemoveAssigneeAction extends Action
{
    public function execute(Report $report, int $userId): Report
    {
        CheckRolesAction::resolve()->execute('manage-assignees');
        $assigner = request()->user();
        $report->assignees()->detach($userId);

        try {
            SendAppNotificationAction::resolve()->execute(
                userId: (int) $userId,
                payload: [
                    'type' => 'report_unassigned',
                    'report_id' => (string) $report->id,
                    'report_number' => $report->number,
                    'report_title' => $report->title,
                    'report_status' => $report->status,
                    'assigner_name' => $assigner->name ?? 'Unknown',
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send assignment notification', [
                'user_id' => $userId,
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);
        }
        \Domain\Shared\Services\AuditLogger::log('report.assign.remove', 'report_assignees', $report->id, ['user_id' => $userId]);
        return $report->load('assignees');
    }
}
