<?php
namespace Infra\Report\Models\Evidence;

use Illuminate\Support\Facades\Storage;
use Infra\Report\Models\Report;
use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;
use Infra\User\Models\User;

class Evidence extends BaseModel
{
    use HasUuidV7;

    protected $table = 'report_evidences';

    protected $casts = [
        'exif' => 'array',
    ];

    // Hide raw EXIF blob from API responses to avoid exposing large/complex object
    protected $hidden = ['exif'];

    protected $appends = ['url'];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        if (! $this->object_key) {
            return null;
        }

        $ttl = (int) config('report.evidence.presign_ttl', 900);
        if ($ttl <= 0) {
            $ttl = 900;
        }

        $adapter = Storage::disk('s3');

        try {
            return $adapter->temporaryUrl($this->object_key, now()->addSeconds($ttl));
        } catch (\Throwable) {
            // Fallback below when temporary URLs are not supported by the disk/driver.
        }

        try {
            return $adapter->url($this->object_key);
        } catch (\Throwable) {
            return null;
        }
    }
}
