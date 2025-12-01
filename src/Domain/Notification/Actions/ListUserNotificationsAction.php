<?php

namespace Domain\Notification\Actions;

use Illuminate\Support\Arr;
use Infra\Shared\Foundations\Action;
use Domain\Shared\Actions\CheckRolesAction;
use Infra\Shared\Models\Notification;

class ListUserNotificationsAction extends Action
{
    public function execute(int $userId, array $filters = [])
    {
        CheckRolesAction::resolve()->execute('list-notifications');
        if (empty($filters)) {
            $filters = request()->query();
        }
        $select = Arr::get($filters, 'select');
        $base = Notification::where('user_id', $userId)->orderByDesc('created_at');
        if ($select === 'yes') {
            return $base->limit(100)->get();
        }
        $per = (int) Arr::get($filters, 'page_size', 10);
        if ($per < 1) $per = 1;
        $page = Arr::get($filters, 'page');
        $page = $page !== null ? max(1, (int) $page) : null;
        return $base->paginate($per, ['*'], 'page', $page);
    }
}
