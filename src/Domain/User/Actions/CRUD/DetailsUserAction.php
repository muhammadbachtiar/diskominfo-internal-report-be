<?php

namespace Domain\User\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;

class DetailsUserAction extends Action
{
    protected $user;

    public function execute($query, User $user)
    {
        CheckRolesAction::resolve()->execute('view-user');
        $this->user = $user;
        if (Arr::exists($query, 'with')) {
            if (Arr::exists($query, 'only')) {
                $this->handleOnly($query['with'], $query['only']);
            } else {
                $this->handleWith($query['with']);
            }
        }

        return $this->user;
    }

    protected function handleWith($relationship)
    {
        $with = explode(',', $relationship);
        $this->user = $this->user->load($with);
    }

    protected function handleOnly($with, $only)
    {
        $with = explode(',', $with);
        $only = explode(',', $only);
        $this->user = $this->user->load($with);
        foreach ($only as $col) {
            $table = explode('.', $col);
            $this->user->{$table[0]}->transform(function ($item) use ($table) {
                return $item->{$table[1]};
            });
        }
    }
}
