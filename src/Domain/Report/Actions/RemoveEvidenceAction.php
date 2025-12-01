<?php

namespace Domain\Report\Actions;

use Domain\Report\Enums\ReportStatus;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Report\Models\Evidence\Evidence;
use Infra\Report\Models\Report;
use Domain\Shared\Services\AuditLogger;
use Infra\Shared\Foundations\Action;

class RemoveEvidenceAction extends Action
{
    public function execute(Report $report, Evidence $evidence): void
    {
        CheckRolesAction::resolve()->execute('delete-evidence');
        if (! in_array($report->status, [ReportStatus::Draft->value, ReportStatus::Revision->value], true)) {
            throw new \RuntimeException('Evidence cannot be removed after approval');
        }
        if ($evidence->report_id !== $report->id) {
            throw new \RuntimeException('Evidence does not belong to the report');
        }
        $id = (string) $evidence->id;
        $evidence->delete();
        AuditLogger::log('evidence.delete', 'report_evidences', $id);
    }
}
