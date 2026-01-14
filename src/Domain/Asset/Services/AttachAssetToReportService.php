<?php

namespace Domain\Asset\Services;

use Carbon\CarbonImmutable;
use Domain\Asset\DTOs\AssetAttachmentItem;
use Domain\Asset\DTOs\AttachAssetToReportInput;
use Domain\Asset\Entities\AssetStatusHistory;
use Domain\Asset\Enums\AssetStatus;
use Domain\Asset\Repositories\AssetRepositoryInterface;
use Domain\Asset\Repositories\AssetStatusHistoryRepositoryInterface;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
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

    /**
     * Execute single asset attachment (backward compatibility)
     */
    public function execute(string $assetId, string $reportId, ?string $note = null): void
    {
        $input = new AttachAssetToReportInput(
            reportId: $reportId,
            assets: [new AssetAttachmentItem($assetId, $note)]
        );

        $this->executeBatch($input);
    }

    /**
     * Execute batch asset attachment
     */
    public function executeBatch(AttachAssetToReportInput $input): array
    {
        // Validate input
        if (!$input->isValid()) {
            throw new \InvalidArgumentException('Invalid input: ' . json_encode($input->validate()));
        }

        // Find report
        $report = Report::query()->find($input->reportId);
        if (!$report) {
            throw (new ModelNotFoundException())->setModel('reports', [$input->reportId]);
        }

        $reportLabel = $report->number ?? $report->id;
        $results = [];

        // Use transaction for batch operations
        DB::transaction(function () use ($input, $report, $reportLabel, &$results) {
            foreach ($input->assets as $assetItem) {
                try {
                    $this->attachSingleAsset($assetItem, $input->reportId, $report, $reportLabel);
                    $results[] = [
                        'asset_id' => $assetItem->assetId,
                        'success' => true,
                        'message' => 'Asset attached successfully',
                    ];
                } catch (\Exception $e) {
                    $results[] = [
                        'asset_id' => $assetItem->assetId,
                        'success' => false,
                        'message' => $e->getMessage(),
                    ];
                    // Re-throw to rollback transaction
                    throw $e;
                }
            }
        });

        return $results;
    }

    /**
     * Attach a single asset to report
     */
    protected function attachSingleAsset(
        AssetAttachmentItem $assetItem,
        string $reportId,
        Report $report,
        string $reportLabel
    ): void {
        // Find asset
        $asset = $this->assets->find($assetItem->assetId);
        if (!$asset) {
            throw (new ModelNotFoundException())->setModel('assets', [$assetItem->assetId]);
        }

        // Attach to report
        $this->assets->attachToReport($assetItem->assetId, $reportId, $assetItem->note);

        // Log audit
        AuditLogger::log('asset.attach_report', 'report_assets', $assetItem->assetId, [
            'report_id' => $reportId,
            'note' => $assetItem->note,
        ]);

        // Record status history
        $this->statusHistories->record(new AssetStatusHistory(
            id: (string) Uuid::uuid7(),
            assetId: $assetItem->assetId,
            status: AssetStatus::Attached,
            changedAt: CarbonImmutable::now(),
            changedBy: auth()->id(),
            note: $assetItem->note
                ? "{$assetItem->note} | Laporan : {$reportLabel}"
                : sprintf('Asset attached to report %s', $reportLabel),
        ));
    }
}
