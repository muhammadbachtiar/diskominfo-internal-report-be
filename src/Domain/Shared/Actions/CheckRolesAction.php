<?php

namespace Domain\Shared\Actions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Infra\Shared\Foundations\Action;

class CheckRolesAction extends Action
{
    public function execute(string $function)
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        if ($user->roles()->where('nama', 'admin')->exists()) {
            return true;
        }

        // Mengecek apakah pengguna memiliki peran yang memiliki permission dengan function tertentu
        $hasPermission = $user->roles()
            ->whereHas('permission', function ($query) use ($function) {
                $query->where('function', $function);
            })
            ->exists();

        // Jika tidak ada permission untuk function tertentu, lempar Forbidden
        if (! $hasPermission) {
            throw new AuthorizationException('Anda tidak memiliki izin untuk tindakan ini.');
        }

        return true;
    }
}
