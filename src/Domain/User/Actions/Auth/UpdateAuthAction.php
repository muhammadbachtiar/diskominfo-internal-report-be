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

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        $user->update($data);

        return $user;
    }
}