<?php

namespace App\Http\Middleware;

use Closure;
use Domain\Shared\Actions\CheckRolesAction;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        CheckRolesAction::resolve()->execute($permission);
        return $next($request);
    }
}

