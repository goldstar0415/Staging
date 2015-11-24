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
        if ($e instanceof UnauthorizedException) {
            return response()->json(['message' => 'user unauthorized'], 401);
        } elseif ($e instanceof NotFoundHttpException) {
            return response()->json(['message' => 'not found'], $e->getStatusCode());
        } elseif ($e instanceof PermissionDeniedException) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } elseif ($e instanceof ModelNotFoundException) {
            return response()->json(['message' => 'not found'], 404);
        } elseif ($e instanceof HttpException) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        } elseif ($e instanceof TokenException) {
            if ($request->is('google-contacts')) {
                return GoogleClient::getContactsEngine()->provider->redirect();
            }
        }

        return parent::render($request, $e);
    }
}
