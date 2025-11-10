<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class Location extends BaseModel
{
    use HasUuidV7, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'longitude',
        'latitude',
    ];

    protected $casts = [
        'longitude' => 'decimal:7',
        'latitude' => 'decimal:7',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
