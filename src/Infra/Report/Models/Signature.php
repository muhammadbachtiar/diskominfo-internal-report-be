<?php
namespace Infra\Report\Models;

use Infra\Shared\Concerns\HasUuidV7;
use Infra\Shared\Models\BaseModel;

class Signature extends BaseModel
{
    use HasUuidV7;

    protected $casts = [
        'ocsp_status' => 'array',
        'tsa_timestamp' => 'array',
        'signed_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(Report::class, 'report_id');
    }
}

