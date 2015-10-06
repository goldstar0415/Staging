<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Class JsonPayloadParse
 * @package App\Http\Middleware
 */
class JsonPayloadParse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->has('payload')) {
            $request->replace(
                array_merge(json_decode($request->input('payload'), true), $request->except('payload'))
            );
        }
        return $next($request);
    }
}
