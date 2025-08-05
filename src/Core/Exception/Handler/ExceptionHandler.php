<?php

declare(strict_types=1);

namespace IronFlow\Core\Exception\Handler;

use IronFlow\Core\Exception\BaseException;
use IronFlow\Core\Exception\HttpException;
use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

class ExceptionHandler extends BaseException
{
    /**
     * Gère les exceptions non capturées dans l'application
     */
    public function handle(\Throwable $exeption, ?Request $request = null): Response
    {
        // Log the exception or perform any other necessary actions
        error_log($exeption->getMessage(), $exeption->getCode());


        // For simplicity, we will just return a response with the exception message and code

        if ($exeption instanceof BaseException) {
            $message = $exeption->getErrorMessage();
            $code = $exeption->getCode();
            $trace = $exeption->getTraceAsString();
            $requestInfo = $request ? sprintf('Request: %s %s', $request->getMethod(), $request->getUri()) : 'No request info';
            error_log($requestInfo);
            error_log($trace);
        } else {
            $message = 'An unexpected error occurred';
            $code = 500;
        }

        // Create a response with the error message and code
        return new Response($message, $code);
    }

    public function handleException(\Throwable $exception, ?Request $request = null): Response
    {
        // Handle specific exceptions if needed
        if ($exception instanceof HttpException) {
            return $this->handleHttpException($exception);
        }

        // For other exceptions, use the generic handler
        return $this->handle($exception, $request);
    }

    private function handleHttpException(HttpException $exception): Response
    {
       return new Response($exception->getMessage(), $exception->getCode());
    }
}