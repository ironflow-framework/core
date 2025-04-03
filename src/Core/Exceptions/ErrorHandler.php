<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

use IronFlow\Core\Application\Application;
use Throwable;
use IronFlow\Http\Response;
use IronFlow\Http\Exceptions\HttpException;
use IronFlow\Support\Facades\Config;


class ErrorHandler
{
   private static bool $isRegistered = false;

   private Application $app;

   /**
    * Constructeur
    * 
    * @param Application $app
    */
   public function __construct(Application $app)
   {
      $this->app = $app;
   }

   public static function register(): void
   {
      if (self::$isRegistered) {
         return;
      }

      error_reporting(E_ALL);
      set_error_handler([self::class, 'handleError']);
      set_exception_handler([self::class, 'handleException']);
      register_shutdown_function([self::class, 'handleFatalError']);

      self::$isRegistered = true;
   }

   public static function handleError(int $severity, string $message, string $file, int $line): bool
   {
      if (!(error_reporting() & $severity)) {
         return false;
      }

      throw new \ErrorException($message, 0, $severity, $file, $line);
   }

   public static function handleException(Throwable $e): void
   {
      $statusCode = match (get_class($e)) {
         'IronFlow\Http\Exceptions\NotFoundException' => 404,
         'IronFlow\Http\Exceptions\ForbiddenException' => 403,
         'IronFlow\Http\Exceptions\UnauthorizedException' => 401,
         'IronFlow\Http\Exceptions\CsrfTokenException' => 419,
         'IronFlow\Http\Exceptions\TooManyRequestsException' => 429,
         default => 500
      };

      if (Config::get('app.debug', false)) {
         $response = Response::view('errors/debug', [
            'exception' => $e,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
         ], $statusCode);
      } else {
         $response = Response::view("errors/{$statusCode}", [], $statusCode);
      }

      $response->send();
      exit;
   }

   public static function handleFatalError(): void
   {
      $error = error_get_last();
      if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
         self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
      }
   }

   private static function getStatusCodeFromException(Throwable $exception): int
   {
      if ($exception instanceof HttpException) {
         return $exception->getStatusCode();
      }

      return match (get_class($exception)) {
         'IronFlow\Http\Exceptions\NotFoundException' => 404,
         'IronFlow\Http\Exceptions\ForbiddenException' => 403,
         'IronFlow\Http\Exceptions\UnauthorizedException' => 401,
         default => 500
      };
   }

   public function getApp(): Application
   {
      return $this->app;
   }
}
