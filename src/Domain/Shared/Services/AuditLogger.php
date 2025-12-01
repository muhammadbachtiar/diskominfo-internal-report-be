<?php

namespace Domain\Shared\Services;

use Infra\Shared\Models\AuditLog;

class AuditLogger
{
    public static function log(string $action, string $entity, string $entityId, array $diff = []): void
    {
        AuditLog::create([
            'actor_id' => auth()->id(),
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'diff_json' => $diff,
            'ip' => request()->ip(),
            'ua' => request()->userAgent(),
        ]);
    }
}

