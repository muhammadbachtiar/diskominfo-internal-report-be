<?php

namespace Domain\User\Actions\CRUD;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Facades\Auth;
use Infra\Shared\Foundations\Action;
use Infra\User\Models\User;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class DeleteUserAction extends Action
{
    public function execute(User $user)
    {
        CheckRolesAction::resolve()->execute('delete-user');

        if ($user->id == Auth::user()->id || $user->id == 1) {
            throw new BadRequestException('user id Tidak bisa dihapus');
        }
        $user->delete();

        return true;

    }
}
