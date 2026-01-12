<?php

namespace Domain\Asset\Actions;

use Carbon\CarbonImmutable;
use Dompdf\Dompdf;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;
use InvalidArgumentException;

class GenerateAssetHandoverPdfAction extends Action
{
    private array $dayNames = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    private array $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    private array $numberWords = [
        1 => 'Satu', 2 => 'Dua', 3 => 'Tiga', 4 => 'Empat', 5 => 'Lima',
        6 => 'Enam', 7 => 'Tujuh', 8 => 'Delapan', 9 => 'Sembilan', 10 => 'Sepuluh',
        11 => 'Sebelas', 12 => 'Dua Belas', 13 => 'Tiga Belas', 14 => 'Empat Belas',
        15 => 'Lima Belas', 16 => 'Enam Belas', 17 => 'Tujuh Belas', 18 => 'Delapan Belas',
        19 => 'Sembilan Belas', 20 => 'Dua Puluh', 21 => 'Dua Puluh Satu',
        22 => 'Dua Puluh Dua', 23 => 'Dua Puluh Tiga', 24 => 'Dua Puluh Empat',
        25 => 'Dua Puluh Lima', 26 => 'Dua Puluh Enam', 27 => 'Dua Puluh Tujuh',
        28 => 'Dua Puluh Delapan', 29 => 'Dua Puluh Sembilan', 30 => 'Tiga Puluh',
        31 => 'Tiga Puluh Satu',
    ];

    public function execute(array $payload): array
    {
        CheckRolesAction::resolve()->execute('view-asset');

        // Parse date
        $date = CarbonImmutable::parse($payload['date']);
        
        // Format nomor
        $nomor = 'Nomor : _______ /DISKOMINFO SP-I/' . $date->year;

        // Format date in Indonesian
        $dateData = [
            'date_day' => $this->dayNames[$date->dayOfWeekIso],
            'date_date' => $this->numberWords[$date->day],
            'date_month' => $this->monthNames[$date->month],
            'date_year' => $this->convertYearToWords($date->year),
            'date_numeric' => $date->format('d-m-Y'),
            'date_year_numeric' => $date->year,
        ];

        // Process assets
        $assetIds = array_column($payload['assets'], 'asset_id');
        $assetDescriptions = array_column($payload['assets'], 'description', 'asset_id');

        // Fetch assets individually
        $assets = collect();
        foreach ($assetIds as $assetId) {
            $asset = Asset::query()->find($assetId);
            
            if (!$asset) {
                throw new InvalidArgumentException("Asset with ID {$assetId} not found");
            }
            
            $assets->push($asset);
        }

        // Fetch all categories separately
        $categoryIds = $assets->pluck('category_id')->unique()->filter();
        $categories = \Infra\Asset\Models\AssetCategory::query()
            ->whereIn('id', $categoryIds->toArray())
            ->get()
            ->keyBy('id');

        // Group assets by category
        $grouped = [];
        foreach ($assets as $asset) {
            $categoryId = $asset->category_id ?? 'no-category';
            $categoryName = $categoryId !== 'no-category' && isset($categories[$categoryId])
                ? $categories[$categoryId]->name
                : 'Tanpa Kategori';

            if (!isset($grouped[$categoryId])) {
                $grouped[$categoryId] = [
                    'category_name' => $categoryName,
                    'count' => 0,
                    'items' => [],
                ];
            }

            $grouped[$categoryId]['count']++;
            $grouped[$categoryId]['items'][] = [
                'name' => $asset->name,
                'number' => $asset->code . ($asset->serial_number ? ' / ' . $asset->serial_number : ''),
                'description' => $assetDescriptions[$asset->id] ?? 'Baik',
            ];
        }

        // Convert to indexed array
        $groupedAssets = array_values($grouped);

        // Render view
        $html = view('pdf.asset-handover', [
            'nomor' => $nomor,
            'first_party' => $payload['first_party'],
            'second_party' => $payload['second_party'],
            'knowing' => $payload['knowing'],
            'grouped_assets' => $groupedAssets,
            ...$dateData,
        ])->render();

        // Generate PDF
        $dompdf = new Dompdf(['isRemoteEnabled' => true]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $output = $dompdf->output();

        // Generate filename
        $filename = 'BAST-' . $date->format('Ymd') . '.pdf';

        return [
            'content' => $output,
            'filename' => $filename,
        ];
    }

    private function convertYearToWords(int $year): string
    {
        $thousands = (int)floor($year / 1000);
        $hundreds = (int)floor(($year % 1000) / 100);
        $tens = $year % 100;

        $result = [];

        if ($thousands > 0) {
            $result[] = $this->numberWords[$thousands] . ' Ribu';
        }

        if ($hundreds > 0) {
            $result[] = $this->numberWords[$hundreds] . ' Ratus';
        }

        if ($tens > 0) {
            $result[] = $this->numberWords[$tens] ?? $this->convertTensToWords($tens);
        }

        return implode(' ', $result);
    }

    private function convertTensToWords(int $number): string
    {
        if ($number < 20) {
            return $this->numberWords[$number] ?? '';
        }

        $tens = (int)floor($number / 10);
        $ones = $number % 10;

        $tensWords = [
            2 => 'Dua Puluh',
            3 => 'Tiga Puluh',
            4 => 'Empat Puluh',
            5 => 'Lima Puluh',
            6 => 'Enam Puluh',
            7 => 'Tujuh Puluh',
            8 => 'Delapan Puluh',
            9 => 'Sembilan Puluh',
        ];

        $result = $tensWords[$tens] ?? '';
        if ($ones > 0) {
            $result .= ' ' . $this->numberWords[$ones];
        }

        return $result;
    }
}
