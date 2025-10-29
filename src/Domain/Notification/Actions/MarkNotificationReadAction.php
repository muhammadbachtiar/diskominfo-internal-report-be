<?php

namespace Domain\Notification\Actions;

use Infra\Shared\Foundations\Action;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Models\Notification;

class MarkNotificationReadAction extends Action
{
    public function execute(Notification $notification): Notification
    {
        CheckRolesAction::resolve()->execute('mark-notification');
        $notification->update(['read_at' => now()]);
        return $notification->refresh();
    }
}
