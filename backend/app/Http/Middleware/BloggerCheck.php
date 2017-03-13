<?php

namespace App\Http\Middleware;

use App\Exceptions\PermissionDeniedException;
use Closure;

/**
 * Class BloggerCheck
 * @package App\Http\Middleware
 */
class BloggerCheck
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
        if (!$request->user()->hasRole('blogger')) {
            throw new PermissionDeniedException;
        }

        return $next($request);
    }
}
