<?php

namespace Infra\Asset\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;

class AssetAttachment extends BaseModel
{
    use HasUuidV7;
    use SoftDeletes;

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'original_size' => 'integer',
        'is_compressed' => 'boolean',
        'is_scanned' => 'boolean',
        'scan_result' => 'array',
        'tags' => 'array',
    ];

    protected $appends = ['url'];

    protected $fillable = [
        'asset_id',
        'uploaded_by',
        'original_name',
        'file_category',
        'mime_type',
        'file_size',
        'object_key',
        'checksum',
        'storage_path',
        'width',
        'height',
        'is_compressed',
        'original_size',
        'is_scanned',
        'scan_status',
        'scan_result',
        'tags',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'asset_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by')
            ->select(['id', 'name', 'email']);
    }

    /**
     * Check if the attachment is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the attachment is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if the attachment is a document
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Get human-readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if file is clean (scanned and no threats)
     */
    public function isScanClean(): bool
    {
        return $this->is_scanned && $this->scan_status === 'clean';
    }

    /**
     * Check if file has threats
     */
    public function hasScanThreats(): bool
    {
        return $this->is_scanned && $this->scan_status === 'infected';
    }

    /**
     * Get temporary URL for the attachment
     * Follows the same pattern as Evidence model
     */
    public function getUrlAttribute(): ?string
    {
        if (! $this->object_key) {
            return null;
        }

        // Use asset config or fallback to 15 minutes (900 seconds)
        $ttl = (int) config('asset.attachment.presign_ttl', 900);
        if ($ttl <= 0) {
            $ttl = 900;
        }

        $adapter = \Illuminate\Support\Facades\Storage::disk('s3');

        try {
            return $adapter->temporaryUrl($this->object_key, now()->addSeconds($ttl));
        } catch (\Throwable) {
            // Fallback when temporary URLs are not supported by the disk/driver
        }

        try {
            return $adapter->url($this->object_key);
        } catch (\Throwable) {
            return null;
        }
    }
}