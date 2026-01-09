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

        $query = Asset::query();

        $this->handleWith($query, $filters);

        if ($status = Arr::get($filters, 'status')) {
            if (in_array($status, ['maintenance', 'retired'])) {
                $query->where('status', $status);
            } elseif ($status === 'available') {
                $query->whereNull('location_id');
            } elseif ($status === 'borrowed') {
                $query->whereNotNull('location_id');
            }
        }

        if ($locationId = Arr::get($filters, 'location_id')) {
            $query->where('location_id', $locationId);
        }

        if ($unitId = Arr::get($filters, 'unit_id')) {
            $query->where('unit_id', $unitId);
        }

        if ($categoryId = Arr::get($filters, 'category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($fromYear = Arr::get($filters, 'from')) {
            $query->whereYear('purchased_at', '>=', $fromYear);
        }

        if ($toYear = Arr::get($filters, 'to')) {
            $query->whereYear('purchased_at', '<=', $toYear);
        }

        if ($search = Arr::get($filters, 'search')) {
            $query->where(function ($builder) use ($search) {
                $pattern = '%' . $search . '%';
                $builder->where('name', 'ilike', $pattern)
                    ->orWhere('code', 'ilike', $pattern)
                    ->orWhere('category', 'ilike', $pattern)
                    ->orWhere('serial_number', 'ilike', $pattern)
                    ->orWhereHas('category', function ($q) use ($pattern) {
                    $q->where('name', 'ilike', $pattern);
                });
            });
        }

        if (Str::lower((string) Arr::get($filters, 'select')) === 'yes') {
            return $query->limit(100)->get();
        }

        $perPage = max(1, (int) Arr::get($filters, 'page_size', 10));
        $page = Arr::get($filters, 'page');

        return $query->paginate($perPage, ['*'], 'page', $page ? max(1, (int) $page) : null);
    }

    protected function handleWith($query, array $filters): void
    {
        $mandatoryRelations = ['currentLoan', 'category'];

        $query->with($mandatoryRelations);

        if ($with = Arr::get($filters, 'with')) {
            $requestedRelations = is_string($with) ? explode(',', $with) : $with;

            if (in_array('unit', $requestedRelations)) {
                $query->with('unit');
            }
        }
    }

}

