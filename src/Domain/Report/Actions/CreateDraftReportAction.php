<?php

namespace Domain\Report\Actions;

use Domain\Report\Enums\ReportStatus;
use Domain\Report\Services\GeoUtils;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;
use Ramsey\Uuid\Uuid;

class CreateDraftReportAction extends Action
{
    public function execute(array $input): Report
    {
        CheckRolesAction::resolve()->execute('create-report');
        $unitId = $input['unit_id'] ?? Auth::user()->unit_id;
        $number = $this->generateNumber($unitId);
        $lat = $input['lat'] ?? null; $lng = $input['lng'] ?? null; $acc = $input['accuracy'] ?? null;
        $geohash = ($lat && $lng) ? GeoUtils::geohash((float) $lat, (float) $lng, 9) : null;
        $report = Report::create([
            'id' => (string) Uuid::uuid7(),
            'number' => $number,
            'title' => $input['title'],
            'description' => $input['description'] ?? null,
            'category' => $input['category'] ?? null,
            'location' => $input['location'] ?? null,
            'category_id' => $input['category_id'] ?? null,
            'lat' => $lat,
            'lng' => $lng,
            'accuracy' => $acc,
            'geohash' => $geohash,
            'event_at' => $input['event_at'] ?? null,
            'unit_id' => $unitId,
            'created_by' => Auth::id(),
            'status' => ReportStatus::Draft->value,
        ]);
        AuditLogger::log('report.create', 'reports', $report->id, $report->toArray());
        return $report;
    }

    protected function generateNumber(string $unitId): string
    {
        $unitCode = optional(Unit::find($unitId))->code ?: 'UNIT';
        $year = date('Y');
        $seq = str_pad((string) (Report::whereYear('created_at', $year)->count() + 1), 5, '0', STR_PAD_LEFT);
        return "LAP/{$unitCode}/{$year}/{$seq}";
    }
}
