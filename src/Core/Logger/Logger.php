<?php

declare(strict_types=1);

namespace IronFlow\Core\Logger;

use IronFlow\Core\Contracts\LoggerInterface;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class Logger implements LoggerInterface
{
   private static ?Logger $instance = null;
   private MonologLogger $logger;

   private function __construct()
   {
      $this->logger = new MonologLogger('ironflow');

      // Handler pour les logs quotidiens avec rotation
      $dailyHandler = new RotatingFileHandler(
         storage_path('logs/ironflow.log'),
         30, // Garder 30 jours de logs
         MonologLogger::DEBUG
      );

      // Handler pour les erreurs critiques
      $errorHandler = new StreamHandler(
         storage_path('logs/error.log'),
         MonologLogger::ERROR
      );

      // Format personnalisé
      $formatter = new LineFormatter(
         "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
         "Y-m-d H:i:s"
      );

      $dailyHandler->setFormatter($formatter);
      $errorHandler->setFormatter($formatter);

      $this->logger->pushHandler($dailyHandler);
      $this->logger->pushHandler($errorHandler);
   }

   public static function getInstance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   public function emergency(string $message, array $context = []): void
   {
      $this->logger->emergency($message, $context);
   }

   public function debug(string $message, array $context = []): void
   {
      $this->logger->debug($message, $context);
   }

   public function info(string $message, array $context = []): void
   {
      $this->logger->info($message, $context);
   }

   public function notice(string $message, array $context = []): void
   {
      $this->logger->notice($message, $context);
   }

   public function alert(string $message, array $context = []): void
   {
      $this->logger->alert($message, $context);
   }

   public function warning(string $message, array $context = []): void
   {
      $this->logger->warning($message, $context);
   }

   public function error(string $message, array $context = []): void
   {
      $this->logger->error($message, $context);
   }

   public function critical(string $message, array $context = []): void
   {
      $this->logger->critical($message, $context);
   }
}
