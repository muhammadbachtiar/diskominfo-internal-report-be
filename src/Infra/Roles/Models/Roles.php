<?php

namespace Infra\Roles\Models;

use Infra\Roles\Models\Permissions\Permissions;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;
use Infra\Village\Models\Village;

class Roles extends BaseModel
{
    public function user()
    {
        return $this->belongsToMany(related: User::class, table: 'user_roles');
    }
    public function village()
    {
        return $this->belongsTo(related: Village::class, foreignKey: 'village_id');
    }
    public function permission()
    {
        return $this->belongsToMany(related: Permissions::class, table: 'roles_permissions', relatedPivotKey: 'permission_id');
    }
}
