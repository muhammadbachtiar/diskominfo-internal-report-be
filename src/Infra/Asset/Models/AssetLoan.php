<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;

class AssetLoan extends BaseModel
{
    use HasUuidV7;

    protected $casts = [
        'loan_lat' => 'float',
        'loan_long' => 'float',
        'borrowed_at' => 'datetime',
        'returned_at' => 'datetime',
        'pic_name' => 'string',
        'note' => 'string',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function borrower(): BelongsTo
    {
        return $this->belongsTo(User::class, 'borrower_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(AssetLocation::class);
    }
}
