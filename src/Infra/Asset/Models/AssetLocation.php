<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class AssetLocation extends BaseModel
{
    use HasUuidV7;

    protected $casts = [
        'lat' => 'float',
        'longitude' => 'float',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(AssetLoan::class, 'asset_loan_id');
    }
}
