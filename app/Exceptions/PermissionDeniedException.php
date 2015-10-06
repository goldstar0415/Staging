<?php

namespace App\Exceptions;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class PermissionDeniedException
 * @package App\Exceptions
 *
 * Throws when user hasn't permissions to access something
 */
class PermissionDeniedException extends HttpException
{
    /**
     * {@inheritDoc}
     */
    public function __construct(
        $statusCode = 403,
        $message = "Permission denied",
        \Exception $previous = null,
        array $headers = array(),
        $code = 0
    ) {
        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}
