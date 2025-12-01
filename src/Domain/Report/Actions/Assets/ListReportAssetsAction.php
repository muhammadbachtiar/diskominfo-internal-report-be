<?php

namespace Domain\Report\Actions\Assets;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Asset as AssetModel;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class ListReportAssetsAction extends Action
{
    public function execute(Report $report)
    {
        CheckRolesAction::resolve()->execute('view-report');

        return $report->assets()
            ->with('unit:id,name,code')
            ->withPivot('note', 'created_at', 'updated_at')
            ->orderBy('report_assets.created_at')
            ->get()
            ->map(function (AssetModel $asset) {
                $assetData = $asset->only([
                    'id',
                    'name',
                    'code',
                    'status',
                    'category',
                    'serial_number',
                    'purchase_price',
                    'purchased_at',
                    'unit_id',
                    'created_at',
                    'updated_at',
                ]);

                $assetData['unit'] = $asset->unit?->only(['id', 'name', 'code']);

                return [
                    'asset' => $assetData,
                    'note' => $asset->pivot->note,
                    'attached_at' => optional($asset->pivot->created_at)->toJSON(),
                    'attached_updated_at' => optional($asset->pivot->updated_at)->toJSON(),
                ];
            });
    }
}
