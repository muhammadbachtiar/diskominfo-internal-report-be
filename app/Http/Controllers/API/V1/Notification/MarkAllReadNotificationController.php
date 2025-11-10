<?php

namespace App\Http\Controllers\API\V1\Notification;

use Domain\Notification\Actions\MarkAllNotificationsReadAction;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class MarkAllReadNotificationController extends BaseController
{
    public function __invoke()
    {
        try {
            $count = MarkAllNotificationsReadAction::resolve()->execute(auth()->id());
            return $this->resolveForSuccessResponseWith('All read', ['updated' => $count]);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

