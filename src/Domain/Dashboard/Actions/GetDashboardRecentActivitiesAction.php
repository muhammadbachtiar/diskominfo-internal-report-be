<?php

namespace Domain\Dashboard\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infra\Asset\Models\AssetLoan;
use Infra\Letter\Models\Letter;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class GetDashboardRecentActivitiesAction extends Action
{
    public function execute(): array
    {
        CheckRolesAction::resolve()->execute('view-dashboard');

        $user = Auth::user();

        // ── 5 Laporan terbaru yang terkait dengan user login ───────────────
        $recentReports = Report::query()
            ->with(['creator:id,name', 'category:id,name'])
            ->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereIn('id', DB::table('report_assignees')
                      ->select('report_id')
                      ->where('user_id', $user->id));
            })
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'title'        => $r->title,
                'status'       => $r->status,
                'category'     => optional($r->category)->name,
                'created_by'   => optional($r->creator)->name,
                'updated_at'   => $r->updated_at?->toIso8601String(),
            ]);

        // ── 5 Peminjaman aset aktif terakhir ──────────────────────────────
        $recentLoans = AssetLoan::query()
            ->with([
                'asset:id,name,code',
                'borrower:id,name',
            ])
            ->whereNull('returned_at')
            ->orderBy('borrowed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($l) => [
                'id'           => $l->id,
                'asset_id'     => $l->asset_id,
                'asset_name'   => optional($l->asset)->name,
                'asset_code'   => optional($l->asset)->code,
                'borrower'     => optional($l->borrower)->name,
                'borrowed_at'  => $l->borrowed_at?->toIso8601String(),
                'pic'          => $l->pic_name,
                'note'         => $l->note,
            ]);

        // ── 5 Surat terbaru ───────────────────────────────────────────────
        $recentLetters = Letter::query()
            ->with(['creator:id,name', 'classification:id,name'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($l) => [
                'id'             => $l->id,
                'type'           => $l->type,
                'letter_number'  => $l->letter_number,
                'subject'        => $l->subject,
                'sender_receiver' => $l->sender_receiver,
                'date_of_letter' => $l->date_of_letter?->toDateString(),
                'classification' => optional($l->classification)->name,
                'created_by'     => optional($l->creator)->name,
            ]);

        return [
            'recent_reports' => $recentReports,
            'recent_loans'   => $recentLoans,
            'recent_letters' => $recentLetters,
        ];
    }
}
