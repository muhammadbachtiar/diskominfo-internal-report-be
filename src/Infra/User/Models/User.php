<?php

namespace Infra\User\Models;

use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Storage;
use Infra\Roles\Models\Roles;
use Infra\Shared\Models\AuthModel;
use Infra\Shared\Models\Unit;

class User extends AuthModel
{
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['avatar_url'];

    public function roles()
    {
        return $this->belongsToMany(related: Roles::class, table: 'user_roles');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the avatar URL attribute.
     *
     * @return string|null
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return Storage::disk('s3')->url($this->avatar);
        }
        
        return null;
    }

    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
