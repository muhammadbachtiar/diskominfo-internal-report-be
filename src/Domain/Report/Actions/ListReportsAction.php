<?php

namespace Domain\Report\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Domain\Shared\Services\IncludeParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Infra\Report\Models\Report;
use Infra\Shared\Foundations\Action;

class ListReportsAction extends Action
{
    public function execute(array $filters)
    {
        CheckRolesAction::resolve()->execute('view-report');

        $allowedIncludes = config('report.report_allowed_includes', []);
        $includes = IncludeParser::parse(Arr::get($filters, 'with'), $allowedIncludes);

        $query = Report::query();

        if (! empty($includes)) {
            $query->with($includes);
        }
        // RBAC scoping for list
        $user = Auth::user();
        $roleNames = $user->roles()->pluck('nama')->toArray();
        $isAdmin = in_array('admin', $roleNames, true);
        $isKadin = in_array('kadin', $roleNames, true);
        $isKabid = in_array('kabid', $roleNames, true);
        $isPegawai = in_array('pegawai', $roleNames, true);

        if (! $isAdmin && ! $isKadin) {
            if ($isKabid) {
                $query = $query->where('unit_id', $user->unit_id);
            } elseif ($isPegawai) {
               $query = $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                      ->orWhereIn('id', DB::table('report_assignees')->select('report_id')->where('user_id', $user->id));
                });
            }
        }
        if (!empty(Arr::get($filters, 'status'))) $query=$query->where('status', Arr::get($filters, 'status'));
        if (!empty(Arr::get($filters, 'unit'))) $query=$query->where('unit_id', Arr::get($filters, 'unit'));
        $search = Arr::get($filters, 'search', Arr::get($filters, 'q'));
        if (!empty($search)) {
            $prefix = rtrim($search, '%');
            $query = $query->where(function($q) use ($search, $prefix) {
                // Prefix match to enable index usage where possible
                $q->where('title', 'like', '%'.$prefix.'%')
                  ->orWhere('number', 'like', '%'.$prefix.'%')
                  ->orWhere('category', 'like', '%'.$prefix.'%')
                  // Description can stay contains due to free text
                  ->orWhere('description', 'like', '%'.$search.'%');
            });
        }
        if (!empty(Arr::get($filters, 'from'))) $query = $query->whereDate('created_at', '>=', Arr::get($filters, 'from'));
        if (!empty(Arr::get($filters, 'to'))) $query = $query->whereDate('created_at', '<=', Arr::get($filters, 'to'));

        $select = Arr::get($filters, 'select');
        if ($select === 'yes') {
            return $query->limit(100)->get();
        }
        $per = (int) Arr::get($filters, 'page_size', 10);
        if ($per < 1) $per = 1;
        $page = Arr::get($filters, 'page');
        $page = $page !== null ? max(1, (int) $page) : null;
        return $query->paginate($per, ['*'], 'page', $page);
    }
}
