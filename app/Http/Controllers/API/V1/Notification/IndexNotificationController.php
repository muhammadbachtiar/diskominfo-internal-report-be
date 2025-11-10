<?php

namespace App\Http\Controllers\API\V1\Notification;

use Domain\Notification\Actions\ListUserNotificationsAction;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class IndexNotificationController extends BaseController
{
    public function __invoke(\Illuminate\Http\Request $req)
    {
        try {
            $data = ListUserNotificationsAction::resolve()->execute(auth()->id(), $req->query());
            if ($req->query('select') === 'yes') {
                return $this->resolveForSuccessResponseWith('Notifications', $data);
            }
            return $this->resolveForSuccessResponseWithPage('Notifications', $data);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
