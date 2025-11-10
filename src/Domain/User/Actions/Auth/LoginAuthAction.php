<?php

namespace Domain\User\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Infra\Shared\Foundations\Action;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class LoginAuthAction extends Action
{
    public function execute($data)
    {
        $success = false;
        $database = null;
        if (Auth::attempt($data)) {
            $database['user'] = Auth::user();
            $roles = $this->handleRoles(Auth::user()->roles()->get());
            $database['token'] = Auth::user()->createToken('Core')->accessToken;
            $success = true;
        }
        if (! $success) {
            throw new BadRequestException('Email atau Password Salah');
        }

        return $database;
    }

    public function handleRoles($data)
    {
        $roles = [];
        foreach ($data as $role) {
            $roles[] = $role->nama;
        }

        return $roles;
    }
}
