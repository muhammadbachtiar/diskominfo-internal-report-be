<?php
namespace Domain\User\Actions\Auth;

use Infra\Shared\Foundations\Action;

class UpdateAuthAction extends Action
{
    public function execute($data)
    {
        $user = auth()->user();
        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        // Prepare update data (only fields that are present)
        $updateData = [];

        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }

        if (isset($data['password']) && !empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }

        // Only update if there's data to update
        if (!empty($updateData)) {
            $user->update($updateData);
        }

        // Refresh user data
        $user->refresh();

        return $user;
    }
}