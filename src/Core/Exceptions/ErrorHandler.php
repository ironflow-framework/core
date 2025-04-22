<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

use IronFlow\Core\Logger\Logger;
use IronFlow\Http\Response;
use Throwable;

class ErrorHandler
{
   private Logger $logger;
   private bool $debug;

   public function __construct()
   {
      $this->logger = Logger::getInstance();
      $this->debug = config('app.debug', false);

      // Définir les gestionnaires d'erreurs
      set_error_handler([$this, 'handleError']);
      set_exception_handler([$this, 'handleException']);
      register_shutdown_function([$this, 'handleShutdown']);
   }

   public function handleError(int $level, string $message, string $file = '', int $line = 0): bool
   {
      if (error_reporting() & $level) {
         throw new \ErrorException($message, 0, $level, $file, $line);
      }

      return false;
   }

   public function handleException(Throwable $exception): void
   {
      $this->logException($exception);

      if (php_sapi_name() === 'cli') {
         $this->renderCliException($exception);
      } else {
         $this->renderHttpException($exception);
      }
   }

   public function handleShutdown(): void
   {
      $error = error_get_last();

      if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
         $this->handleError(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
         );
      }
   }

   private function logException(Throwable $exception): void
   {
      $this->logger->error(
         $exception->getMessage(),
         [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
         ]
      );
   }

   private function renderCliException(Throwable $exception): void
   {
      $output = PHP_EOL . "Exception: " . get_class($exception) . PHP_EOL;
      $output .= "Message: " . $exception->getMessage() . PHP_EOL;

      if ($this->debug) {
         $output .= "File: " . $exception->getFile() . ":" . $exception->getLine() . PHP_EOL;
         $output .= "Stack trace:" . PHP_EOL . $exception->getTraceAsString() . PHP_EOL;
      }

      fwrite(STDERR, $output);
      exit(1);
   }

   private function renderHttpException(Throwable $exception): void
   {
      $statusCode = $this->getStatusCode($exception);

      if ($this->debug) {
         $response = Response::view('errors.debug', [
            'exception' => $exception,
            'trace' => $exception->getTraceAsString(),
            'message' => $exception->getMessage(),
         ], $statusCode);
      } else {
         $response = Response::view('errors.' . $statusCode, [
            'message' => $this->getPublicMessage($exception),
         ], $statusCode);
      }

      $response->send();
   }

   private function getStatusCode(Throwable $exception): int
   {
      if ($exception instanceof HttpException) {
         return $exception->getStatusCode();
      }

      return 500;
   }

   private function getPublicMessage(Throwable $exception): string
   {
      if ($exception instanceof HttpException) {
         return $exception->getMessage();
      }

      return 'Une erreur est survenue. Veuillez réessayer plus tard.';
   }
}
