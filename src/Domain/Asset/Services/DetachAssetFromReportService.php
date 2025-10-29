<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;
use Ramsey\Uuid\Uuid;

class DetachAssetFromReportService extends Action
{
    public function __construct(
        protected AssetRepositoryInterface $assets,
        protected AssetStatusHistoryRepositoryInterface $statusHistories,
    ) {
    }

    public function execute(string $assetId, string $reportId): void
    {
        $asset = $this->assets->find($assetId);
        if (! $asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetId]);
        }

        $report = Report::query()->find($reportId);
        if (! $report) {
            throw (new ModelNotFoundException())->setModel('reports', [$reportId]);
        }

        $isAttached = DB::table('report_assets')
            ->where('report_id', $reportId)
            ->where('asset_id', $assetId)
            ->exists();

        if (! $isAttached) {
            throw (new ModelNotFoundException())->setModel('report_assets', [$reportId, $assetId]);
        }

        $this->assets->detachFromReport($assetId, $reportId);

        $reportLabel = $report->number ?? $report->id;

        AuditLogger::log('asset.detach_report', 'report_assets', $assetId, [
            'report_id' => $reportId,
        ]);

        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetId,
            status: $asset->status,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: sprintf('Asset detached from report %s', $reportLabel),
        ));
    }
}
