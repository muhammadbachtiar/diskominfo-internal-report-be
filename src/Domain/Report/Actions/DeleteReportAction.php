<?php

namespace Domain\Report\Actions;

use Domain\Report\Enums\ReportStatus;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Report\Models\Report;
use Domain\Shared\Services\AuditLogger;
use Infra\Shared\Foundations\Action;

class DeleteReportAction extends Action
{
    public function execute(Report $report): void
    {
        CheckRolesAction::resolve()->execute('delete-report');
        if (! in_array($report->status, [ReportStatus::Draft->value, ReportStatus::Revision->value], true)) {
            throw new \RuntimeException('Report cannot be deleted in current status');
        }
        $id = $report->id;
        $report->delete();
        AuditLogger::log('report.delete', 'reports', $id);
    }
}
