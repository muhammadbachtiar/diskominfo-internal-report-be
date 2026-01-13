<?php

namespace Infra\Letter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Infra\Shared\Models\BaseModel;
use Infra\Shared\Models\Unit;
use Infra\User\Models\User;
use Infra\Letter\Models\Classification;

class Letter extends BaseModel
{
    protected $table = 'letters';

    protected $fillable = [
        'type',
        'letter_number',
        'sender_receiver',
        'date_of_letter',
        'year',
        'subject',
        'classification_id',
        'unit_id',
        'description',
        'file_url',
        'thumbnail_url',
        'metadata_ai',
        'created_by',
    ];

    protected $casts = [
        'date_of_letter' => 'date',
        'metadata_ai' => 'array',
        'year' => 'integer',
    ];

    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByUnit($query, $unitId)
    {
        if ($unitId) {
            return $query->where('unit_id', $unitId);
        }
        return $query;
    }
}
