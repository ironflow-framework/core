<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

use Exception;
use Throwable;

/**
 * Gestionnaire d'erreurs centralisé pour le framework IronFlow
 */
class ErrorHandler
{
   /**
    * @var array Options de configuration
    */
   protected array $options = [
      'displayErrors' => true,
      'logErrors' => true,
      'errorLogFile' => null
   ];

   /**
    * @var array Types d'erreurs à gérer
    */
   protected array $errorTypes = [
      E_ERROR => 'Error',
      E_WARNING => 'Warning',
      E_PARSE => 'Parse Error',
      E_NOTICE => 'Notice',
      E_CORE_ERROR => 'Core Error',
      E_CORE_WARNING => 'Core Warning',
      E_COMPILE_ERROR => 'Compile Error',
      E_COMPILE_WARNING => 'Compile Warning',
      E_USER_ERROR => 'User Error',
      E_USER_WARNING => 'User Warning',
      E_USER_NOTICE => 'User Notice',
      E_STRICT => 'Strict Standards',
      E_RECOVERABLE_ERROR => 'Recoverable Error',
      E_DEPRECATED => 'Deprecated',
      E_USER_DEPRECATED => 'User Deprecated'
   ];

   /**
    * @var array Rappels par type d'exception
    */
   protected array $exceptionHandlers = [];

   /**
    * Constructeur du gestionnaire d'erreurs
    */
   public function __construct(array $options = [])
   {
      $this->options = array_merge($this->options, $options);

      // Définir le gestionnaire d'erreurs PHP
      set_error_handler([$this, 'handleError']);

      // Définir le gestionnaire d'exceptions
      set_exception_handler([$this, 'handleException']);

      // Enregistrer la fonction de fermeture
      register_shutdown_function([$this, 'handleShutdown']);
   }

   /**
    * Gère les erreurs PHP
    */
   public function handleError(int $level, string $message, string $file, int $line): bool
   {
      // Si l'erreur est désactivée par l'opérateur @, on l'ignore
      if (error_reporting() === 0) {
         return false;
      }

      // Convertir les erreurs en exceptions pour une gestion uniforme
      throw new ErrorException($message, 0, $level, $file, $line);
   }

   /**
    * Gère les exceptions
    */
   public function handleException(Throwable $exception): void
   {
      // Enregistrer l'exception dans les logs
      if ($this->options['logErrors']) {
         $this->logException($exception);
      }

      // Vérifier si un gestionnaire spécifique existe pour ce type d'exception
      $exceptionClass = get_class($exception);
      if (isset($this->exceptionHandlers[$exceptionClass])) {
         call_user_func($this->exceptionHandlers[$exceptionClass], $exception);
         return;
      }

      // Afficher ou non l'erreur selon la configuration
      if ($this->options['displayErrors']) {
         $this->renderException($exception);
      } else {
         $this->renderFriendlyErrorPage();
      }
   }

   /**
    * Gère les erreurs fatales qui déclenchent un arrêt du script
    */
   public function handleShutdown(): void
   {
      $error = error_get_last();

      if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
         $this->handleError(
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
         );
      }
   }

   /**
    * Enregistre l'exception dans les logs
    */
   protected function logException(Throwable $exception): void
   {
      $logFile = $this->options['errorLogFile'] ?? ini_get('error_log');

      $message = sprintf(
         "[%s] %s: %s in %s on line %d\nStack trace: %s",
         date('Y-m-d H:i:s'),
         get_class($exception),
         $exception->getMessage(),
         $exception->getFile(),
         $exception->getLine(),
         $exception->getTraceAsString()
      );

      error_log($message, 3, $logFile);
   }

   /**
    * Affiche une page d'erreur détaillée
    */
   protected function renderException(Throwable $exception): void
   {
      http_response_code(500);

      $exceptionClass = get_class($exception);
      $code = $exception->getCode();
      $message = $exception->getMessage();
      $file = $exception->getFile();
      $line = $exception->getLine();
      $trace = $exception->getTraceAsString();

      // Afficher une page d'erreur détaillée si en mode développement
      include __DIR__ . '/Views/exception.php';
   }

   /**
    * Affiche une page d'erreur conviviale pour l'utilisateur
    */
   protected function renderFriendlyErrorPage(): void
   {
      http_response_code(500);

      // Afficher une page d'erreur conviviale en production
      include __DIR__ . '/Views/error.php';
   }

   /**
    * Enregistre un gestionnaire personnalisé pour un type d'exception spécifique
    */
   public function registerExceptionHandler(string $exceptionClass, callable $handler): self
   {
      $this->exceptionHandlers[$exceptionClass] = $handler;
      return $this;
   }

   /**
    * Configure les options du gestionnaire d'erreurs
    */
   public function configure(array $options): self
   {
      $this->options = array_merge($this->options, $options);
      return $this;
   }

   /**
    * Convertit le niveau d'erreur en chaîne lisible
    */
   protected function getErrorType(int $level): string
   {
      return $this->errorTypes[$level] ?? 'Unknown Error';
   }
}
