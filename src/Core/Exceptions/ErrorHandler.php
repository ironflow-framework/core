<?php

namespace IronFlow\Core\Exceptions;

use Throwable;
use IronFlow\Http\Response;
use IronFlow\Http\Exceptions\HttpException;
use IronFlow\Support\Facades\Config;

class ErrorHandler
{
   private static bool $isRegistered = false;

   public static function register(): void
   {
      if (self::$isRegistered) {
         return;
      }

      error_reporting(E_ALL);
      set_error_handler([self::class, 'handleError']);
      set_exception_handler([self::class, 'handleException']);
      register_shutdown_function([self::class, 'handleShutdown']);

      self::$isRegistered = true;
   }

   public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
   {
      if (error_reporting() & $level) {
         throw new \ErrorException($message, 0, $level, $file, $line);
      }

      return true;
   }

   public static function handleException(Throwable $exception): void
   {
      $statusCode = self::getStatusCodeFromException($exception);

      try {
         if (Config::get('app.debug', false)) {
            $response = Response::view('errors/debug', [
               'exception' => $exception,
               'message' => $exception->getMessage(),
               'file' => $exception->getFile(),
               'line' => $exception->getLine(),
               'trace' => $exception->getTraceAsString()
            ], $statusCode);
         } else {
            $response = Response::view("errors/{$statusCode}", [], $statusCode);
         }

         $response->send();
      } catch (\Throwable $e) {
         // Fallback en cas d'erreur lors du rendu de la vue
         http_response_code($statusCode);
         echo "Une erreur est survenue.";
      }
   }

   public static function handleShutdown(): void
   {
      $error = error_get_last();

      if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
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
}
