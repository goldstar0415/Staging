<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Log;
use Route;

/**
 * Class PrerenderController
 * Render pages for social bots
 * @package App\Http\Controllers
 */
class PrerenderController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['render']]);
    }

    public function render($page_url)
    {
        $route = self::resolve($page_url);

        if (!$route)
            return new Response('Not found', 404);

        return Route::dispatch(Request::create($route, 'GET'));
    }

    final protected static function resolve($url)
    {
        switch (true) {
            case preg_match('/^(\d+)\/spot\/(\d+)/i', $url, $params):
                return sprintf('spots/%s/preview', $params[2]);

            case preg_match('/^(\d+)\/article\/([^\/]+)/i', $url, $params):
                return sprintf('posts/%s/preview', strtolower($params[2]));

            // use '[a-zA-Z0-9]+' instead of '\d+' for hash
            case preg_match('/^areas\/(\d+)/i', $url, $params):
                return sprintf('areas/%s/preview', strtolower($params[1]));

            default:
                return null;
        }
    }

}