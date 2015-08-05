<?php

namespace App\Http\Middleware;

use Closure;

class JsonPayloadParse
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
        if ($request->has('payload')) {
            $request->replace(json_decode($request->input('payload'), true));
        }
        return $next($request);
    }
}
