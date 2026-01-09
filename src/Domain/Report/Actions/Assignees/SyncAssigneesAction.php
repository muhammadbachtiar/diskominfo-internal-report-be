<?php

namespace Domain\Report\Actions\Assignees;

use Domain\Notification\Actions\SendAppNotificationAction;
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

        $assigner = request()->user();
        
        // Validate assignees are in the same unit
        $invalid = User::whereIn('id', $userIds)
            ->where(function ($q) use ($report) {
                $q->whereNull('unit_id')->orWhere('unit_id', '!=', $report->unit_id);
            })
            ->count();
        if ($invalid > 0) {
            throw new \RuntimeException('Assignees must be in the same unit');
        }
        
        // Get current assignees before sync
        $currentAssigneeIds = $report->assignees()->pluck('users.id')->toArray();
        
        // Sync assignees
        $report->assignees()->syncWithoutDetaching($userIds);   
        
        // Determine newly assigned users (not previously assigned)
        $newlyAssignedIds = array_diff($userIds, $currentAssigneeIds);
        
        // Send notifications to newly assigned users
        foreach ($newlyAssignedIds as $userId) {
            try {
                SendAppNotificationAction::resolve()->execute(
                    userId: (int) $userId,
                    payload: [
                        'type' => 'report_assigned',
                        'report_id' => (string) $report->id,
                        'report_number' => $report->number,
                        'report_title' => $report->title,
                        'report_status' => $report->status,
                        'action_url' => "/reports/{$report->id}",
                        'assigner_name' => $assigner->name ?? 'Unknown',
                    ]
                );
            } catch (\Exception $e) {
                // Log error but don't fail the assignment
                \Log::error('Failed to send assignment notification', [
                    'user_id' => $userId,
                    'report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        \Domain\Shared\Services\AuditLogger::log('report.assign.sync', 'report_assignees', $report->id, ['user_ids' => $userIds]);
        
        return $report->load('assignees');
    }
}
