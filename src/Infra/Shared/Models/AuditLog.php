<?php
namespace Infra\Shared\Models;

use Infra\User\Models\User;

class AuditLog extends BaseModel
{
    use \Infra\Shared\Concerns\HasUuidV7;

    protected $table = 'audit_logs';
    protected $casts = [
        'diff_json' => 'array',
    ];

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}

