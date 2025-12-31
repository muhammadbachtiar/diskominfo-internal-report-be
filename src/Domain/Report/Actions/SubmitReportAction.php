<?php

namespace Domain\Report\Actions;

use Domain\Report\Enums\ApprovalStatus;
use Domain\Report\Enums\ReportStatus;
use Domain\Notification\Actions\SendAppNotificationAction;
use Infra\Report\Models\Approval;
use Infra\Report\Models\Report;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Infra\Shared\Foundations\Action;

class SubmitReportAction extends Action
{
    public function execute(Report $report, ?string $note = null): Report
    {
        CheckRolesAction::resolve()->execute('submit-report');
        
        // Update report status to submitted
        $report->update(['status' => ReportStatus::Submitted->value]);
        AuditLogger::log('report.submit', 'reports', $report->id);

        // Create approval record
        Approval::create([
            'report_id' => $report->id,
            'approver_id' => request()->user()->id,
            'status' => ApprovalStatus::Pending->value,
            'note' => $note,
        ]);

        // Send notification to report creator
        if ($report->created_by) {
            try {
                SendAppNotificationAction::resolve()->execute(
                    userId: (int) $report->created_by,
                    payload: [
                        'type' => 'report_submitted',
                        'report_id' => (string) $report->id,
                        'report_number' => $report->number,
                        'report_title' => $report->title,
                        'report_status' => $report->status,
                        'action_url' => "/reports/{$report->id}",
                        'submitted_at' => now()->toISOString(),
                        'submitted_by' => request()->user()->name ?? 'Unknown',
                    ]
                );
            } catch (\Exception $e) {
                // Log error but don't fail the submission
                \Log::error('Failed to send submission notification', [
                    'report_id' => $report->id,
                    'creator_id' => $report->created_by,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        return $report->refresh();
    }
}
