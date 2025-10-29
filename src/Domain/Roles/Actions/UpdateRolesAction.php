<?php

namespace Domain\Roles\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Roles\Models\Roles;
use Infra\Shared\Foundations\Action;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UpdateRolesAction extends Action
{
    public function execute($data, Roles $role)
    {
        CheckRolesAction::resolve()->execute('edit-role');
        if ($role->id == 1) {
            throw new BadRequestException('Super Admin Role tidak dapat di ubah');
        }
        if (Arr::exists($data, 'permissions')) {
            $permission = $data['permissions'];
            $this->handlePermission($permission, $role);
            $data = Arr::except($data, 'permission');
        }
        $role->update($data);

        return $role;
    }

    protected function handlePermission($permission, $role)
    {
        $role->permission()->sync($permission);
    }
}
