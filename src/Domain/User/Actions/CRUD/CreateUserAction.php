<?php

namespace Domain\User\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use Infra\User\Models\UserRoles\UserRoles;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class CreateUserAction extends Action
{
    public function execute($data)
    {
        CheckRolesAction::resolve()->execute('add-user');

        $array = ['email', 'password'];
        foreach ($array as $key) {
            if (! Arr::exists($data, $key)) {
                throw new BadRequestException($key.' is required');
            }
        }

        if (auth()->user()->village_id != null) {
            $data['village_id'] = auth()->user()->village_id;
        }
        if (Arr::exists($data, 'roles')) {
            $roles = $data['roles'];
            $data = Arr::except($data, 'roles');
        }
        $data['email'] = Str::lower($data['email']);
        $check = User::where('email', $data['email'])->first();
        if ($check) {
            throw new BadRequestException('email has been used please find other email');
        }
        $data['password'] = bcrypt($data['password']);
        $user = User::create($data);
        if (! empty($roles)) {
            $this->handleAddRoles($roles, $user);
        }

        return $user;
    }

    public function handleAddRoles($roles, User $user)
    {
        foreach ($roles as $role) {
            UserRoles::create([
                'roles_id' => $role,
                'user_id' => $user->id,
            ]);
        }
    }
}
