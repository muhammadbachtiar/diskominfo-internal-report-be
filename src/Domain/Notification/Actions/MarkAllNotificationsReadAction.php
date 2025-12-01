<?php

namespace Domain\Notification\Actions;

use Infra\Shared\Foundations\Action;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Models\Notification;

class MarkAllNotificationsReadAction extends Action
{
    public function execute(int $userId): int
    {
        CheckRolesAction::resolve()->execute('read-all-notifications');
        return Notification::where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
