<?php

namespace Domain\Roles\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Roles\Models\Roles;
use Infra\Shared\Foundations\Action;

class IndexRolesAction extends Action
{
    protected $roles;

    public function execute($query)
    {
        CheckRolesAction::resolve()->execute('view-role');
        $this->roles = Roles::query();
        if(Arr::exists($query, 'village')) {
            $this->roles = $this->roles->where('village_id', $query['village']);
        }
        if(auth()->user()->village_id != null) {
            $this->roles = $this->roles->where('village_id', auth()->user()->village_id);
        }

        if (Arr::exists($query, 'with')) {
            $this->handleWith($query['with']);
        }
        if (Arr::exists($query, 'search')) {
            $this->search($query['search']);
        }
        if (Arr::exists($query, 'total_only') && $query['total_only'] == 'true') {
            $data['total'] = $this->roles->count();

            return $data;
        }
        $page_size = Arr::get($query, 'page_size', 10);
        $page = Arr::get($query, 'page', 1);
        if (Arr::exists($query, 'select2') && $query['select2'] == true) {
            return $this->handleSelect();
        }
        $this->handlePaginate($page_size, $page);

        return $this->roles;
    }

    protected function search($name)
    {
        $prefix = rtrim($name, '%');
        $this->roles = $this->roles->where('nama', 'like', $prefix.'%');
    }

    protected function handleWith($relationship)
    {
        $with = explode(',', $relationship);
        $this->roles = $this->roles->with($with);
    }

    protected function handleSelect()
    {
        return $this->roles->get();
    }

    protected function handlePaginate($page_size = 10, $page = 1)
    {
        $this->roles = $this->roles->paginate($page_size, ['*'], 'page', $page);
    }
}
