<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Guard;

/**
 * Class CheckOnline
 * @package App\Http\Middleware
 */
class CheckOnline
{
    /**
     * @var Guard
     */
    private $auth;

    /**
     * CheckOnline constructor.
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
        if ($this->auth->check()) {
            $this->auth->user()->update(['last_action_at' => Carbon::now()]);
        }

        return $next($request);
    }
}
