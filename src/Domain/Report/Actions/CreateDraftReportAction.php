<?php

namespace Domain\Report\Actions;

use Domain\Asset\DTOs\AssetAttachmentItem;
use Domain\Asset\DTOs\AttachAssetToReportInput;
use Domain\Asset\Services\AttachAssetToReportService;
use Domain\Report\Actions\Assignees\SyncAssigneesAction;
use Domain\Report\Enums\ReportStatus;
use Domain\Report\Services\GeoUtils;
use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\AuditLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;
use Ramsey\Uuid\Uuid;

class CreateDraftReportAction extends Action
{
    public function execute(array $input): Report
    {
        CheckRolesAction::resolve()->execute('create-report');
        
        return DB::transaction(function () use ($input) {
            $unitId = $input['unit_id'] ?? Auth::user()->unit_id;
            $number = $this->generateNumber($unitId);
            $lat = $input['lat'] ?? null;
            $lng = $input['lng'] ?? null;
            $acc = $input['accuracy'] ?? null;
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
            
            // Assign members if user_ids provided
            if (!empty($input['user_ids']) && is_array($input['user_ids'])) {
                try {
                    SyncAssigneesAction::resolve()->execute($report, $input['user_ids']);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            
            // Attach assets if asset_ids provided
            if (!empty($input['asset_ids']) && is_array($input['asset_ids'])) {
                try {
                    $this->attachAssets($report, $input['asset_ids']);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
            
            return $report->fresh(['assignees', 'assets']);
        });
    }

    protected function generateNumber(string $unitId): string
    {
        $unitCode = optional(Unit::find($unitId))->code ?: 'UNIT';
        $year = date('Y');

        $seq = DB::transaction(function () use ($year) {
            $lastReport = Report::withTrashed() 
                ->whereYear('created_at', $year)
                ->lockForUpdate()
                ->orderBy('created_at', 'desc')
                ->first();

            return $lastReport ?
                (int) substr($lastReport->number, -5) + 1 :
                1;
        });

        $seq = str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
        return "LAP/{$unitCode}/{$year}/{$seq}";
    }

    /**
     * Attach assets to report within transaction
     *
     * @param Report $report
     * @param array $assetIds Array of asset IDs or array of ['asset_id' => string, 'note' => string|null]
     * @return void
     */
    protected function attachAssets(Report $report, array $assetIds): void
    {
        // Convert simple array of IDs to AssetAttachmentItem objects
        $assets = array_map(function ($item) {
            if (is_array($item) && isset($item['asset_id'])) {
                // Format: ['asset_id' => 'uuid', 'note' => 'optional note']
                return new AssetAttachmentItem(
                    assetId: $item['asset_id'],
                    note: $item['note'] ?? null
                );
            } else {
                // Format: simple string ID
                return new AssetAttachmentItem(
                    assetId: is_string($item) ? $item : $item['asset_id'],
                    note: null
                );
            }
        }, $assetIds);

        $input = new AttachAssetToReportInput(
            reportId: $report->id,
            assets: $assets
        );

        // Execute batch attachment (already within transaction from execute method)
        AttachAssetToReportService::resolve()->executeBatch($input);
    }
}
