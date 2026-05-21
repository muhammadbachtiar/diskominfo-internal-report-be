<?php

namespace Domain\Dashboard\Actions;

use Carbon\Carbon;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infra\Asset\Models\Asset;
use Infra\Asset\Models\AssetLoan;
use Infra\Letter\Models\Letter;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class GetDashboardSummaryAction extends Action
{
    public function execute(): array
    {
        CheckRolesAction::resolve()->execute('view-dashboard');

        $user     = Auth::user();
        $now      = Carbon::now();
        $yearStart = $now->copy()->startOfYear();
        $monthStart = $now->copy()->startOfMonth();

        // ── Asset Summary ──────────────────────────────────────────────────
        $maintenanceAssets = Asset::query()->where('status', 'maintenance')->count();
        $retiredAssets     = Asset::query()->where('status', 'retired')->count();

        // Active/Borrowed assets (has currentLoan, location_id is not null, status is available)
        $borrowedAssets = Asset::query()
            ->where('status', 'available')
            ->whereNotNull('location_id')
            ->whereHas('currentLoan')
            ->count();

        // Available assets (no currentLoan, location_id is null, status is available)
        $availableAssets = Asset::query()
            ->where('status', 'available')
            ->whereNull('location_id')
            ->whereDoesntHave('currentLoan')
            ->count();

        $totalAssets = Asset::query()->count();

        $assetStatusCounts = [
            'available'   => $availableAssets,
            'borrowed'    => $borrowedAssets,
            'maintenance' => $maintenanceAssets,
            'retired'     => $retiredAssets,
        ];

        // ── Active Assets Map Data (Leaflet) ─────────────────────────────────
        $activeAssetList = Asset::query()
            ->where('status', 'available')
            ->whereNotNull('location_id')
            ->whereHas('currentLoan')
            ->with(['currentLoan.borrower', 'location'])
            ->get()
            ->map(function ($asset) {
                $latitude = null;
                $longitude = null;
                $locationName = null;

                if ($asset->currentLoan) {
                    $latitude = $asset->currentLoan->loan_lat;
                    $longitude = $asset->currentLoan->loan_long;
                    $locationName = $asset->currentLoan->location_name;
                }

                // Fallback to asset's location coordinates/name if loan lacks them
                if (is_null($latitude) || is_null($longitude)) {
                    $latitude = $asset->location->latitude ?? null;
                    $longitude = $asset->location->longitude ?? null;
                }
                if (is_null($locationName)) {
                    $locationName = $asset->location->name ?? null;
                }

                return [
                    'id'            => $asset->id,
                    'name'          => $asset->name,
                    'code'          => $asset->code,
                    'latitude'      => !is_null($latitude) ? (float) $latitude : null,
                    'longitude'     => !is_null($longitude) ? (float) $longitude : null,
                    'location_name' => $locationName,
                    'borrower'      => $asset->currentLoan->borrower->name ?? null,
                    'pic_name'      => $asset->currentLoan->pic_name ?? null,
                ];
            })
            ->filter(fn ($item) => !is_null($item['latitude']) && !is_null($item['longitude']))
            ->values()
            ->toArray();

        // ── Report Summary ─────────────────────────────────────────────────
        $totalReportsThisYear = Report::query()
            ->whereYear('created_at', $now->year)
            ->count();

        // Laporan yang butuh aksi oleh user login (assigned & status draft/submitted)
        $myPendingReports = Report::query()
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereIn('id', DB::table('report_assignees')
                      ->select('report_id')
                      ->where('user_id', $user->id));
            })
            ->whereIn('status', ['draft', 'submitted', 'review', 'revision'])
            ->count();

        // Report per status (untuk chart)
        $reportStatusCounts = Report::query()
            ->whereYear('created_at', $now->year)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // ── Letter Summary ─────────────────────────────────────────────────
        $totalLettersThisYear = Letter::query()
            ->whereYear('created_at', $now->year)
            ->count();

        $totalLettersThisMonth = Letter::query()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $letterTypeCounts = Letter::query()
            ->whereYear('created_at', $now->year)
            ->select('type', DB::raw('count(*) as total'))
            ->groupBy('type')
            ->pluck('total', 'type')
            ->toArray();

        // ── Monthly Trend (Report + Letter per bulan, tahun berjalan) ──────
        $monthlyData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyData[] = [
                'month'   => $m,
                'label'   => Carbon::create($now->year, $m)->translatedFormat('M'),
                'reports' => Report::query()
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $m)
                    ->count(),
                'letters' => Letter::query()
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $m)
                    ->count(),
            ];
        }

        return [
            'assets' => [
                'total'         => $totalAssets,
                'available'     => $availableAssets,
                'borrowed'      => $borrowedAssets,
                'maintenance'   => $maintenanceAssets,
                'retired'       => $retiredAssets,
                'by_status'     => $assetStatusCounts,
                'active_assets' => $activeAssetList,
            ],
            'reports' => [
                'total_this_year'   => $totalReportsThisYear,
                'my_pending_count'  => $myPendingReports,
                'by_status'         => $reportStatusCounts,
            ],
            'letters' => [
                'total_this_year'  => $totalLettersThisYear,
                'total_this_month' => $totalLettersThisMonth,
                'by_type'          => $letterTypeCounts,
            ],
            'monthly_trend' => $monthlyData
        ];
    }
}
