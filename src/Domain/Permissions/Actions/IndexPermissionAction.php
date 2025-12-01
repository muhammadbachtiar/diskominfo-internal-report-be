<?php

namespace Domain\Permissions\Actions;

use Illuminate\Support\Arr;
use Infra\Roles\Models\Permissions\Permissions;
use Infra\Shared\Foundations\Action;

class IndexPermissionAction extends Action
{
    protected $permission;

    public function execute($query)
    {
        $this->permission = Permissions::query();
        if (Arr::exists($query, 'with')) {
            $this->handleWith($query['with']);
        }
        if (Arr::exists($query, 'apps')) {
            $this->handleApps($query['apps']);
        }
        if (Arr::exists($query, 'search')) {
            $this->search($query['search']);
        }
        if (Arr::exists($query, 'total_only') && $query['total_only']) {
            $data['total'] = $this->permission->count();

            return $data;
        }
        if (Arr::exists($query, 'page_size') && Arr::exists($query, 'page')) {
            $this->handlePaginate($query['page_size']);
        } else {
            $this->handlePaginate();
        }

        return $this->permission;
    }

    protected function search($search)
    {
        $this->permission = $this->permission->where('function', 'like', '%'.$search.'%');
    }

    protected function handleWith($relationship)
    {
        $with = explode(',', $relationship);
        $this->permission = $this->permission->with($with);
    }

    protected function handleApps($apps)
    {
        $app = explode(',', $apps);
        $this->permission = $this->permission->whereIn('apps', $app);
    }

    protected function handlePaginate($page_size = 10)
    {
        $this->permission = $this->permission->paginate($page_size);
    }
}
