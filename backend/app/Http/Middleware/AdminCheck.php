<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class AdminCheck
 * @package App\Http\Middleware
 */
class AdminCheck
{
    /**
     * @var Guard
     */
    private $auth;

    /**
     * AdminCheck constructor.
     * @param Guard $auth
     */
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$this->auth->check() or !$this->auth->user()->hasRole('admin')) {
            throw new \App\Exceptions\PermissionDeniedException;
        }

        return $next($request);
    }
}
