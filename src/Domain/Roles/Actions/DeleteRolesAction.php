<?php

namespace Domain\Roles\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Infra\Roles\Models\Roles;
use Infra\Shared\Foundations\Action;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class DeleteRolesAction extends Action
{
    public function execute(Roles $role)
    {
        if ($role->id == 1) {
            throw new BadRequestException('Super Admin Role tidak dapat di hapus');
        }
        CheckRolesAction::resolve()->execute('delete-role');
        $role->delete();

        return true;
    }
}
