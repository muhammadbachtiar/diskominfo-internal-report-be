<?php

namespace Domain\Unit\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Unit;

class IndexUnitAction extends Action
{
    protected $query;

    public function execute(array $filters)
    {
        CheckRolesAction::resolve()->execute('view-unit');

        $this->query = Unit::query();

        $search = Arr::get($filters, 'search');
        if (!empty($search)) {
            $prefix = rtrim($search, '%');
            $this->query->where(function ($q) use ($prefix, $search) {
                $q->where('name', 'ilike', $prefix.'%')
                  ->orWhere('code', 'ilike', $prefix.'%')
                  ->orWhere('name', 'ilike', '%'.$search.'%');
            });
        }

        $select = Arr::get($filters, 'select');
        if ($select === 'yes') {
            return $this->query->limit(100)->get();
        }

        $per = (int) Arr::get($filters, 'page_size', 10);
        if ($per < 1) { $per = 1; }
        $page = Arr::get($filters, 'page');
        $page = $page !== null ? max(1, (int) $page) : null;

        return $this->query->paginate($per, ['*'], 'page', $page);
    }
}

