<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

/**
 * Class XSSProtector
 * @package App\Http\Middleware
 */
class XSSProtector
{
    /**
     * @var Response $response
     */
    protected $response;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $this->response = $next($request);

        if ($this->isJson()) {
            $this->escapeResponse();
        }

        return $this->response;
    }

    /**
     * Check is json response
     *
     * @return bool
     */
    protected function isJson()
    {
         return $this->response->headers->get('Content-Type') === 'application/json';
    }

    /**
     * Escape strings in response
     */
    protected function escapeResponse()
    {
        $data = json_decode($this->response->content(), true);

        array_walk_recursive($data, function (&$item) {
            if (is_string($item)) {
                $item = e($item);
            }
        });

        $this->response->setContent(json_encode($data));
    }
}
