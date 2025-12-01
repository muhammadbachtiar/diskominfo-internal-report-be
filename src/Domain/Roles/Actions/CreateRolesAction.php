<?php

namespace Domain\Roles\Actions;

use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Support\Arr;
use Infra\Roles\Models\Permissions\RolesPermission;
use Infra\Roles\Models\Roles;
use Infra\Shared\Foundations\Action;

class CreateRolesAction extends Action
{
    public function execute($data)
    {
        CheckRolesAction::resolve()->execute('add-role');
        if(auth()->user()->village_id != null) {
            $data['village_id'] = auth()->user()->village_id;
        }
        
        if (Arr::exists($data, 'permissions')) {
            $permission = $data['permissions'];
            $data = Arr::except($data, 'permission');
        }
        $roles = Roles::create($data);
        if (! empty($permission)) {
            $this->handleRolesPermission($roles, $permission);
        }

        return $roles;

    }

    protected function handleRolesPermission($roles, $permission)
    {
        foreach ($permission as $item) {
            RolesPermission::create([
                'roles_id' => $roles->id,
                'permission_id' => $item,
            ]);
        }

    }
}
