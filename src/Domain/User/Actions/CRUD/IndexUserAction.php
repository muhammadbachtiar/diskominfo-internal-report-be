<?php

namespace Domain\User\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;

class IndexUserAction extends Action
{
    protected $user;

    public function execute($query)
    {
        CheckRolesAction::resolve()->execute('view-user');
        $this->user = User::query();
        if (Arr::exists($query, 'total_only') && $query['total_only'] === 'true') {
            $data['total'] = $this->user->count();

            return $data;
        }
        $this->user = $this->user->where('id', '!=', Auth::user()->id)->where('id', '!=', 1);

        $this->applyUnitFilter();

        if (Arr::exists($query, 'search')) {
            $this->search($query['search']);
        }

        if (Arr::exists($query, 'with')) {
            $this->handleWith($query['with']);
        }
        $select = Arr::get($query, 'select');
        if ($select === 'yes') {
            $this->user = $this->user->limit(100)->get();
            return $this->user;
        }
        $page_size = Arr::get($query, 'page_size', 10);
        $page = Arr::get($query, 'page', 1);
        $this->handlePaginate($page_size, $page);

        return $this->user;
    }

     protected function applyUnitFilter()
    {
        $currentUser = Auth::user();
        
        $isSuperadmin = $currentUser->roles()->where('nama', 'admin')->exists();
        $isKadin = $currentUser->roles()->where('nama', 'kadin')->exists();
        
        if (!$isSuperadmin && !$isKadin) {
            $this->user = $this->user->where('unit_id', $currentUser->unit_id);
        }
    }

   protected function search($search)
    {
        $pattern = '%' . $search . '%';
        
        $this->user = $this->user->where(function ($query) use ($pattern) {
            $query->where('name', 'ilike', $pattern)
                ->orWhere('email', 'ilike', $pattern);
        });
    }


    protected function handleWith($relationship)
    {
        $with = explode(',', $relationship);
        $this->user = $this->user->with($with);
    }

    protected function handlePaginate($page_size, $page = 1)
    {
        $this->user = $this->user->paginate($page_size, ['*'], 'page', $page);
    }
}
