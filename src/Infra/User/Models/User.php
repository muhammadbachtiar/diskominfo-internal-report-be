<?php

namespace Infra\User\Models;

use Database\Factories\UserFactory;
use Infra\Roles\Models\Roles;
use Infra\Shared\Models\AuthModel;
use Infra\Shared\Models\Unit;

class User extends AuthModel
{
    public function roles()
    {
        return $this->belongsToMany(related: Roles::class, table: 'user_roles');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
