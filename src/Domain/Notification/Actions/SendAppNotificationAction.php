<?php

namespace Domain\Notification\Actions;

use App\Events\NotificationCreated;
use Domain\Shared\Services\AuditLogger;
use Infra\Shared\Foundations\Action;
use Infra\Shared\Models\Notification;

class SendAppNotificationAction extends Action
{
    public function execute(int $userId, array $payload, ?string $status = 'sent'): Notification
    {
        $notif = Notification::create([
            'user_id' => $userId,
            'channel' => 'app',
            'payload' => $payload,
            'sent_at' => now(),
            'status' => $status,
        ]);

        AuditLogger::log('notify.create', 'notifications', (string) $notif->id, $payload);

        // Broadcast (works with log driver too)
        event(new NotificationCreated($userId, [
            'id' => (string) $notif->id,
            'payload' => $payload,
            'created_at' => $notif->created_at?->toISOString(),
        ]));

        return $notif;
    }
}

