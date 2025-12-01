<?php

namespace Domain\User\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use Infra\User\Models\UserRoles\UserRoles;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateUserAction extends Action
{
    public function execute($data, User $user)
    {
        CheckRolesAction::resolve()->execute('edit-user');
        if ($user->id == 1 && Arr::exists($data, 'password')) {
            throw new BadRequestException('tidak bisa akses');
        }
        if (Arr::exists($data, 'roles')) {
            $this->handleUserRoles($data['roles'], $user);
            $data = Arr::except($data, 'roles');
        }
        if (Arr::exists($data, 'password')) {
            $data['password'] = bcrypt($data['password']);
        }
        $check = User::where('email', $data['email'])->where('email', '!=', $user->email)->first();
        if ($check) {
            throw new BadRequestException('email has been used');
        }
        $user->update($data);

        return $user;

    }

    protected function handleUserRoles($data, User $user)
    {
        UserRoles::where('user_id', $user->id)->delete();
        foreach ($data as $roles) {
            UserRoles::create([
                'user_id' => $user->id,
                'roles_id' => $roles,
            ]);
        }
    }
}
