<?php

namespace Domain\Report\Actions;

use Domain\Report\Enums\ReportStatus;
use Domain\Report\Services\GeoUtils;
use Domain\Shared\Services\AuditLogger;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class UpdateReportAction extends Action
{
    public function execute(Report $report, array $input): Report
    {
        CheckRolesAction::resolve()->execute('update-report');
        if (! in_array($report->status, [ReportStatus::Draft->value, ReportStatus::Revision->value], true)) {
            throw new \RuntimeException('Report cannot be updated in current status');
        }
        $allowed = [
            'title','description','category','location','lat','lng','accuracy','event_at'
        ];
        $data = array_intersect_key($input, array_flip($allowed));
        if (array_key_exists('lat', $data) && array_key_exists('lng', $data) && $data['lat'] !== null && $data['lng'] !== null) {
            $report->geohash = GeoUtils::geohash((float) $data['lat'], (float) $data['lng']);
        }
        foreach ($data as $k => $v) {
            $report->{$k} = $v;
        }
        $report->save();
        AuditLogger::log('report.update', 'reports', $report->id, $data);
        return $report->refresh();
    }
}
