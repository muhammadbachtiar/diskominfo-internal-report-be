<?php

namespace Domain\Asset\Actions;

use Dompdf\Dompdf;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;
use InvalidArgumentException;

class GenerateAssetLabelPdfAction extends Action
{
    public function execute(array $payload): array
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $assetIds = $payload['assets'];

        // Fetch assets without eager loading category
        $assets = Asset::query()
            ->whereIn('id', $assetIds)
            ->get();

        if ($assets->isEmpty()) {
            throw new InvalidArgumentException("No assets found for the provided IDs");
        }

        // Fetch categories separately
        $categoryIds = $assets->pluck('category_id')->unique()->filter();
        $categories = \Infra\Asset\Models\AssetCategory::query()
            ->whereIn('id', $categoryIds->toArray())
            ->get()
            ->keyBy('id');

        // Prepare data for view
        $assetData = $assets->map(function ($asset) use ($categories) {
            $categoryId = $asset->category_id;
            $categoryName = $categoryId && isset($categories[$categoryId]) 
                ? $categories[$categoryId]->name 
                : 'PERANGKAT LUNAK/KERAS';

            return [
                'name' => $asset->name,
                'year' => $asset->purchased_at ? $asset->purchased_at->year : '-',
                'code' => $asset->code,
                'serial_number' => $asset->serial_number,
                'category_name' => $categoryName,
            ];
        });

        // Render view
        $html = view('pdf.asset-label', [
            'assets' => $assetData,
        ])->render();

        // Generate PDF
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        $dompdf->render();
        $output = $dompdf->output();

        // Generate filename
        $filename = 'Asset-Labels-' . now()->format('YmdHis') . '.pdf';

        return [
            'content' => $output,
            'filename' => $filename,
        ];
    }
}
