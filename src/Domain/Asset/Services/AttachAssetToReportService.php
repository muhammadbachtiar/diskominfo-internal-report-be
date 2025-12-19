<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;
use Ramsey\Uuid\Uuid;

class AttachAssetToReportService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
    ) {
    }

    public function execute(string $assetId, string $reportId, ?string $note = null): void
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $report = Report::query()->find($reportId);
        if (! $report) {
            throw (new ModelNotFoundException())->setModel('reports', [$reportId]);
        }

        $this->assets->attachToReport($assetId, $reportId, $note);

        $reportLabel = $report->number ?? $report->id;

        AuditLogger::log('asset.attach_report', 'report_assets', $assetId, [
            'report_id' => $reportId,
            'note' => $note,
        ]);

        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: AssetStatus::Attached,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: $note ? "$note | Laporan : $reportLabel" : sprintf('Asset attached to report %s', $reportLabel),
        ));
    }
}
