<?php

namespace Infra\Report\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Infra\Report\Models\Evidence;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class ReportAction extends BaseModel
{
    use HasUuidV7;

    protected $fillable = [
        'report_id',
        'title',
        'note',
        'sequence',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(Evidence::class, 'action_id');
    }
}
