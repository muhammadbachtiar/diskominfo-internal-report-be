<?php

namespace Domain\User\Actions\Auth;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Infra\Shared\Foundations\Action;

class UploadAvatarAction extends Action
{
    public function execute(UploadedFile $avatar)
    {
        $user = auth()->user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('s3')->exists($user->avatar)) {
            Storage::disk('s3')->delete($user->avatar);
        }

        // Upload new avatar to S3
        $avatarPath = Storage::disk('s3')->put('avatars', $avatar);
        
        // Update user with avatar path
        $user->update(['avatar' => $avatarPath]);
        
        // Refresh to get updated data with avatar_url
        $user->refresh();

        return $user;
    }
}
