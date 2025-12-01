<?php

namespace Infra\Report\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class ReportCategory extends BaseModel
{
    use HasUuidV7, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'category_id');
    }
}
