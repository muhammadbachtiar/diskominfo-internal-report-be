<?php

namespace Domain\Roles\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Roles\Models\Roles;
use Infra\Shared\Foundations\Action;

class DetailRolesAction extends Action
{
    protected $roles;

    public function execute($query, Roles $role)
    {
        CheckRolesAction::resolve()->execute('view-role');
        $this->roles = $role;
        if (Arr::exists($query, 'with')) {
            if (Arr::exists($query, 'only')) {
                $this->handleOnly($query['with'], $query['only']);
            } else {
                $this->handleWith($query['with']);
            }
        }

        return $this->roles;
    }

    protected function handleWith($relationship)
    {
        $with = explode(',', $relationship);
        $this->roles = $this->roles->load($with);

    }

    protected function handleOnly($with, $only)
    {
        $with = explode(',', $with);
        $only = explode(',', $only);
        $this->roles = $this->roles->load($with);
        foreach ($only as $col) {
            $table = explode('.', $col);
            $this->roles->{$table[0]}->transform(function ($item) use ($table) {
                return $item->{$table[1]};
            });
        }
    }
}
