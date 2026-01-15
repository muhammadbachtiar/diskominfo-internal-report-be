<?php

namespace App\Http\Controllers\API\V1\User\Auth;

use App\Http\Requests\UpdateProfileRequest;
use Domain\User\Actions\Auth\UpdateAuthAction;
use Infra\Shared\Controllers\BaseController;

class EditProfileController extends BaseController
{
    public function __invoke(UpdateProfileRequest $req)
    {
        try {
            $data = $req->only(['name', 'email', 'password', 'password_confirmation']);
            
            $user = UpdateAuthAction::resolve()->execute($data);

            return $this->resolveForSuccessResponseWith(
                message: 'Profile updated successfully',
                data: $user
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage()
            );
        }
    }
}
