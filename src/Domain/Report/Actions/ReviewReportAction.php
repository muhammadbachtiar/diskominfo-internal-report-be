<?php

namespace Domain\Report\Actions;

use Carbon\Carbon;
use Domain\Notification\Actions\SendAppNotificationAction;
use Domain\Report\Enums\ApprovalStatus;
use Domain\Report\Enums\ReportStatus;
use Domain\Shared\Services\AuditLogger;
use Infra\Report\Jobs\GeneratePdfJob;
use Infra\Report\Models\Approval;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class ReviewReportAction extends Action
{
    public function execute(Report $report, string $decision, ?string $note = null): Report
    {
        $reviewer = request()->user();
        
        // Create approval record
        $approval = new Approval(['report_id' => $report->id, 'approver_id' => $reviewer->id]);
        $approval->status = match ($decision) {
            'approve' => ApprovalStatus::Approved->value,
            'reject' => ApprovalStatus::Rejected->value,
            'revision' => ApprovalStatus::Revision->value,
            default => ApprovalStatus::Pending->value,
        };
        $approval->note = $note;
        $approval->decided_at = Carbon::now();
        $approval->save();

        // Update report status
        $report->status = match ($approval->status) {
            ApprovalStatus::Approved->value => ReportStatus::Approved->value,
            ApprovalStatus::Rejected->value => ReportStatus::Rejected->value,
            ApprovalStatus::Revision->value => ReportStatus::Revision->value,
            default => $report->status,
        };
        $report->save();
        AuditLogger::log('report.review', 'reports', $report->id, ['status' => $report->status]);

        // Generate PDF if approved
        if ($approval->status === 'approved') {
            GeneratePdfJob::dispatch($report->id);
        }

        // Prepare notification payload
        $decisionLabel = match ($approval->status) {
            ApprovalStatus::Approved->value => 'approved',
            ApprovalStatus::Rejected->value => 'rejected',
            ApprovalStatus::Revision->value => 'needs revision',
            default => 'reviewed',
        };

        $notificationPayload = [
            'type' => 'report_reviewed',
            'report_id' => (string) $report->id,
            'report_number' => $report->number,
            'report_title' => $report->title,
            'report_status' => $report->status,
            'decision' => $approval->status,
            'reviewer_name' => $reviewer->name ?? 'Unknown',
            'reviewed_at' => $approval->decided_at->toISOString(),
            'action_url' => "/reports/{$report->id}",
        ];

        // Notify creator about decision
        if ($report->created_by) {
            try {
                SendAppNotificationAction::resolve()->execute(
                    userId: (int) $report->created_by,
                    payload: $notificationPayload
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send review notification to creator', [
                    'report_id' => $report->id,
                    'creator_id' => $report->created_by,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Notify all assignees (excluding creator to avoid duplicate)
        $report->load('assignees');
        foreach ($report->assignees as $assignee) {
            // Skip if assignee is the creator (already notified)
            if ($assignee->id === $report->created_by) {
                continue;
            }

            try {
                SendAppNotificationAction::resolve()->execute(
                    userId: (int) $assignee->id,
                    payload: $notificationPayload
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send review notification to assignee', [
                    'report_id' => $report->id,
                    'assignee_id' => $assignee->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $report->refresh();
    }
}
