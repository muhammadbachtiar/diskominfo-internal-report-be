<?php
namespace Infra\Shared\Models;

use Infra\Shared\Concerns\HasUuidV7;
use Infra\User\Models\User;

class Notification extends BaseModel
{
    use HasUuidV7;

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
