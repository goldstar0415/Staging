<?php

namespace App\Http\Middleware;

use App\Exceptions\PermissionDeniedException;
use Closure;

/**
 * Class BloggerOrAdmin
 * @package App\Http\Middleware
 */
class BloggerOrAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!($request->user()->hasRole('blogger') || $request->user()->hasRole('admin'))) {
            throw new PermissionDeniedException;
        }

        return $next($request);
    }
}