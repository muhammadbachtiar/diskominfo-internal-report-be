<?php

namespace Domain\User\Actions\Auth;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Infra\Shared\Foundations\Action;

class GetDataAuthAction extends Action
{
    protected $auth;

    public function execute($query)
    {
        $this->auth = Auth::user();
        if (Arr::exists($query, 'with')) {
            $this->handleWith($query['with']);
        }

        return $this->auth;
    }

    protected function handleWith($relationship)
    {
        $with = explode(',', $relationship);
        $this->auth = $this->auth->load($with);
    }
}
