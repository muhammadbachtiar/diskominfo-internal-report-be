<?php

namespace Infra\Report\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class Evidence extends BaseModel
{
    use HasUuidV7;

    protected $fillable = [
        'report_id',
        'action_id',
        'type',
        'original_name',
        'mime',
        'size',
        'object_key',
        'checksum',
        'exif',
        'lat',
        'lng',
        'accuracy',
        'geohash',
        'phash',
        'scan_status',
        'uploaded_by'
    ];

    protected $casts = [
        'exif' => 'array',
        'lat' => 'float',
        'lng' => 'float',
        'accuracy' => 'float',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function action(): BelongsTo
    {
        return $this->belongsTo(ReportAction::class);
    }
}
