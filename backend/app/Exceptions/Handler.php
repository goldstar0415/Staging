<?php

namespace App\Exceptions;

use App\Http\Controllers\SocialContactsController;
use App\Services\Social\GoogleClient;
use Config;
use Exception;
use Illuminate\Contracts\Validation\UnauthorizedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        if(app()->environment('production') && $this->shouldReport($e)) {
            app('sentry')->captureException($e);
        }

        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        $response = null;

        switch (true) {
            case $e instanceof UnauthorizedException: {
                $response = response()->json(['message' => 'user unauthorized'], 401);
                break;
            }
            case $e instanceof NotFoundHttpException: {
                $response = response()->json(['message' => 'not found'], $e->getStatusCode());
                break;
            }
            case $e instanceof PermissionDeniedException: {
                $response = response()->json(['message' => $e->getMessage()], $e->getStatusCode());
                break;
            }
            case $e instanceof ModelNotFoundException: {
                $response = response()->json(['message' => 'not found'], 404);
                break;
            }
            case $e instanceof HttpException: {
                $response = response()->json(['message' => $e->getMessage()], $e->getStatusCode());
                break;
            }
            case $e instanceof TokenException: {
                if ($request->is('google-contacts')) {
                    return GoogleClient::getContactsEngine()->provider->redirect();
                }
                break;
            }
        }

        if ($response) {
            // Add CORS headers
            app('Asm89\Stack\CorsService')->addActualRequestHeaders($response, $request);
            return $response;
        }

        return parent::render($request, $e);
    }
}
