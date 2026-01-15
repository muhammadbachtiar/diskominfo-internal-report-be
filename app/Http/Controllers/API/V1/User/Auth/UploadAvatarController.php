<?php

namespace App\Http\Controllers\API\V1\User\Auth;

use Domain\User\Actions\Auth\UploadAvatarAction;
use Illuminate\Http\Request;
use Infra\Shared\Controllers\BaseController;

class UploadAvatarController extends BaseController
{
    public function __invoke(Request $req)
    {
        try {
            $req->validate([
                'avatar' => 'required|file|image|mimes:jpeg,jpg,png,gif|max:2048',
            ]);

            $user = UploadAvatarAction::resolve()->execute($req->file('avatar'));

            return $this->resolveForSuccessResponseWith(
                message: 'Avatar uploaded successfully',
                data: $user
            );
        } catch (\Throwable $th) {
            return $this->resolveForFailedResponseWith(
                message: $th->getMessage()
            );
        }
    }
}
