<?php

namespace Domain\Asset\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Infra\Asset\Models\Asset;
use Infra\Shared\Foundations\Action;

class IndexAssetAction extends Action
{
    public function execute(array $filters): LengthAwarePaginator|iterable
    {
        CheckRolesAction::resolve()->execute('view-asset');

        $query = Asset::query()->with(['unit', 'currentLoan', 'category']);

        if ($status = Arr::get($filters, 'status')) {
            $query->where('status', $status);
        }

        if ($unitId = Arr::get($filters, 'unit_id')) {
            $query->where('unit_id', $unitId);
        }

        if ($search = Arr::get($filters, 'q')) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('code', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('serial_number', 'like', '%' . $search . '%');
            });
        }

        if (Str::lower((string) Arr::get($filters, 'select')) === 'yes') {
            return $query->limit(100)->get();
        }

        $perPage = max(1, (int) Arr::get($filters, 'page_size', 10));
        $page = Arr::get($filters, 'page');

        return $query->paginate($perPage, ['*'], 'page', $page ? max(1, (int) $page) : null);
    }
}

