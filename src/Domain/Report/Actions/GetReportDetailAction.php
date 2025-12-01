<?php

namespace Domain\Report\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\IncludeParser;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class GetReportDetailAction extends Action
{
    public function execute(Report $report, array $options = []): Report
    {
        CheckRolesAction::resolve()->execute('view-report');
        $allowedIncludes = config('report.report_allowed_includes', []);
        $includes = IncludeParser::parse($options['with'] ?? null, $allowedIncludes);
        if (!empty($includes)) {
            $report->load($includes);
        }
        return $report;
    }
}
