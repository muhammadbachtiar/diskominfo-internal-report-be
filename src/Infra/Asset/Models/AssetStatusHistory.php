<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Domain\Asset\Enums\AssetStatus;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;

class AssetStatusHistory extends BaseModel
{
    use HasUuidV7;

    protected $casts = [
        'changed_at' => 'datetime',
        'status_key' => AssetStatus::class,
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
