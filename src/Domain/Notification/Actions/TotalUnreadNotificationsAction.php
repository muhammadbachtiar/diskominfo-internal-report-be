<?php

namespace Domain\Notification\Actions;

use Infra\Shared\Foundations\Action;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Models\Notification;

class TotalUnreadNotificationsAction extends Action
{
    public function execute(int $userId)
    {
        CheckRolesAction::resolve()->execute('list-notifications');
        $base = Notification::where('user_id', $userId)->orderByDesc('created_at');

        return ["total_unread" => $base->whereNull('read_at')->count()];
    }
}
