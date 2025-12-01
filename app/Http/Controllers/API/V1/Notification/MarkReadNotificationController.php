<?php

namespace App\Http\Controllers\API\V1\Notification;

use Domain\Notification\Actions\MarkNotificationReadAction;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;
use Infra\Shared\Models\Notification;

class MarkReadNotificationController extends BaseController
{
    public function __invoke(Notification $notification)
    {
        try {
            if ($notification->user_id !== auth()->id()) {
                return $this->resolveForFailedResponseWith('Forbidden', status: HttpStatus::Forbidden);
            }
            $data = MarkNotificationReadAction::resolve()->execute($notification);
            return $this->resolveForSuccessResponseWith('Read', $data);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}

