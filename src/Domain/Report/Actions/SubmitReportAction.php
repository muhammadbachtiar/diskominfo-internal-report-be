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
    public function execute(Report $report): Report
    {
        CheckRolesAction::resolve()->execute('submit-report');
        $report->update(['status' => ReportStatus::Submitted->value]);
        AuditLogger::log('report.submit', 'reports', $report->id);

        // Create pending approval for Kabid (approver selection is business logic; placeholder null approver)
        $approval = Approval::create([
            'report_id' => $report->id,
            'approver_id' => request()->user()->id, // Replace with real Kabid selection
            'status' => ApprovalStatus::Pending->value,
        ]);
        // Notify Kabid (placeholder approver)
        SendAppNotificationAction::resolve()->execute($approval->approver_id, [
            'type' => 'report_submitted',
            'report_id' => (string) $report->id,
            'number' => $report->number,
            'title' => $report->title,
        ]);
        return $report->refresh();
    }
}
