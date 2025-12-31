<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Infra\Report\Models\Report;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\Shared\Models\Unit;

class Asset extends BaseModel
{
    use HasUuidV7;
    use SoftDeletes;

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(AssetLoan::class);
    }
    
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }
    public function currentLoan(): HasOne
    {
        return $this->hasOne(AssetLoan::class)->whereNull('returned_at');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(AssetStatusHistory::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class);
    }

    public function reports(): BelongsToMany
    {
        return $this->belongsToMany(Report::class, 'report_assets', 'asset_id', 'report_id')
            ->withPivot('note')
            ->withTimestamps();
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AssetAttachment::class);
    }
}
