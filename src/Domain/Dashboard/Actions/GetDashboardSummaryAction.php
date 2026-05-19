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
        $assetStatusCounts = Asset::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $totalAssets     = array_sum($assetStatusCounts);
        $availableAssets = $assetStatusCounts['available'] ?? 0;
        $borrowedAssets  = $assetStatusCounts['borrowed'] ?? 0;
        $maintenanceAssets = $assetStatusCounts['maintenance'] ?? 0;
        $retiredAssets   = $assetStatusCounts['retired'] ?? 0;

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
                'total'       => $totalAssets,
                'available'   => $availableAssets,
                'borrowed'    => $borrowedAssets,
                'maintenance' => $maintenanceAssets,
                'retired'     => $retiredAssets,
                'by_status'   => $assetStatusCounts,
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
            'monthly_trend' => $monthlyData,
        ];
    }
}
