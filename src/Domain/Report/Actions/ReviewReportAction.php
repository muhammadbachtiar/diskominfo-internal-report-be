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
        $approval = Approval::where('report_id', $report->id)->latest()->first();
        if (! $approval) {
            $approval = new Approval(['report_id' => $report->id, 'approver_id' => request()->user()->id]);
        }
        $approval->status = match ($decision) {
            'approve' => ApprovalStatus::Approved->value,
            'reject' => ApprovalStatus::Rejected->value,
            'revision' => ApprovalStatus::Revision->value,
            default => ApprovalStatus::Pending->value,
        };
        $approval->note = $note;
        $approval->decided_at = Carbon::now();
        $approval->save();

        $report->status = match ($approval->status) {
            ApprovalStatus::Approved->value => ReportStatus::Approved->value,
            ApprovalStatus::Rejected->value => ReportStatus::Rejected->value,
            ApprovalStatus::Revision->value => ReportStatus::Revision->value,
            default => $report->status,
        };
        $report->save();
        AuditLogger::log('report.review', 'reports', $report->id, ['status' => $report->status]);

        if ($approval->status === 'approved') {
            // Dispatch jobs for PDF and signature
            GeneratePdfJob::dispatch($report->id);
        }

        // Notify creator about decision
        SendAppNotificationAction::resolve()->execute($report->created_by, [
            'type' => 'report_reviewed',
            'report_id' => (string) $report->id,
            'number' => $report->number,
            'decision' => $approval->status,
            'note' => $approval->note,
        ]);

        return $report->refresh();
    }
}
