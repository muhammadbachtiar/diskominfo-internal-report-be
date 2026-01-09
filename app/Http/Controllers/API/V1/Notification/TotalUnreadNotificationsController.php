<?php

namespace App\Http\Controllers\API\V1\Notification;

use Domain\Notification\Actions\TotalUnreadNotificationsAction;
use Illuminate\Validation\ValidationException;
use Infra\Shared\Controllers\BaseController;
use Infra\Shared\Enums\HttpStatus;

class TotalUnreadNotificationsController extends BaseController
{
    public function __invoke(\Illuminate\Http\Request $req)
    {
        try {
            $data = TotalUnreadNotificationsAction::resolve()->execute(auth()->id());
            return $this->resolveForSuccessResponseWith('Notifications', $data);
        } catch (ValidationException $th) {
            return $this->resolveForFailedResponseWith('Validation Error', $th->errors(), HttpStatus::UnprocessableEntity);
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith($th->getMessage());
        }
    }
}
