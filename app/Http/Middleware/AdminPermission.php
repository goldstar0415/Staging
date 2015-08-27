<?php

namespace App\Http\Middleware;

use App\Exceptions\PermissionDeniedException;
use Closure;

class AdminPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     * @throws PermissionDeniedException
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if (!isset($user) or !$user->hasRole('admin')) {
            throw new PermissionDeniedException;
        }

        return $next($request);
    }
}
