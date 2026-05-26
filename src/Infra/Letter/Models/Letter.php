<?php

namespace Infra\Letter\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
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

    protected $appends = [
        'letter_url',
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

    /**
     * Get the absolute URL of the file.
     */
    public function getFileUrlAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return asset($value);
        }
        if (str_starts_with($value, 'storage/')) {
            return asset('/' . $value);
        }

        $diskName = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        return Storage::disk($diskName)->url($value);
    }

    /**
     * Get the absolute URL of the thumbnail, with fallback.
     */
    public function getThumbnailUrlAttribute($value): ?string
    {
        if (empty($value)) {
            // Fallback for image
            if ($this->file_url) {
                return $this->file_url;
            }
            return null;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (str_starts_with($value, '/storage/')) {
            return asset($value);
        }
        if (str_starts_with($value, 'storage/')) {
            return asset('/' . $value);
        }

        $diskName = config('filesystems.disks.s3.bucket') ? 's3' : 'public';
        return Storage::disk($diskName)->url($value);
    }

    /**
     * Virtual attribute for letter_url mapped from file_url.
     */
    public function getLetterUrlAttribute(): ?string
    {
        return $this->file_url;
    }
}
