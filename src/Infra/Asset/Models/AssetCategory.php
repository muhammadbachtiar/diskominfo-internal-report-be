<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class AssetCategory extends BaseModel
{
    use HasUuidV7, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
