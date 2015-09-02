<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\UnauthorizedException;
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
        } elseif ($e instanceof HttpException) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return parent::render($request, $e);
    }
}
