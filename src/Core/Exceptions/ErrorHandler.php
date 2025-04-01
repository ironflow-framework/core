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
      error_log("=== Début de la gestion de l'exception ===");
      error_log("Type d'exception: " . get_class($exception));
      error_log("Message: " . $exception->getMessage());
      error_log("Fichier: " . $exception->getFile());
      error_log("Ligne: " . $exception->getLine());
      error_log("Trace: " . $exception->getTraceAsString());

      $statusCode = self::getStatusCodeFromException($exception);
      error_log("Code HTTP: " . $statusCode);

      try {
         if (Config::get('app.debug', false)) {
            error_log("Mode debug activé, affichage de la vue de débogage");
            $response = Response::view('errors/debug', [
               'exception' => $exception,
               'message' => $exception->getMessage(),
               'file' => $exception->getFile(),
               'line' => $exception->getLine(),
               'trace' => $exception->getTraceAsString()
            ], $statusCode);
         } else {
            error_log("Mode debug désactivé, affichage de la vue d'erreur standard");
            $response = Response::view("errors/{$statusCode}", [], $statusCode);
         }

         error_log("Envoi de la réponse...");
         $response->send();
         error_log("Réponse envoyée avec succès");
      } catch (\Throwable $e) {
         error_log("ERREUR lors du rendu de la vue: " . $e->getMessage());
         error_log("Trace: " . $e->getTraceAsString());
         // Fallback en cas d'erreur lors du rendu de la vue
         http_response_code($statusCode);
         echo "Une erreur est survenue.";
      }

      error_log("=== Fin de la gestion de l'exception ===");
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
