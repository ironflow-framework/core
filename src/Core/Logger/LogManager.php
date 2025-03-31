<?php

namespace IronFlow\Core\Logger;

use Monolog\Logger;


class LogManager
{
   private static ?LogManager $instance = null;
   private Logger $logger;

   private function __construct()
   {
      $this->logger = new Logger('IronFlow');
   }

   public function info(string $message, array $context = []): void
   {
      $this->logger->info($message, $context);
   }

   public function error(string $message, array $context = []): void
   {
      $this->logger->error($message, $context);
   }

   public function warning(string $message, array $context = []): void
   {
      $this->logger->warning($message, $context);
   }

   public function debug(string $message, array $context = []): void
   {
      $this->logger->debug($message, $context);
   }

   public function critical(string $message, array $context = []): void
   {
      $this->logger->critical($message, $context);
   }

   public function alert(string $message, array $context = []): void
   {
      $this->logger->alert($message, $context);
   }

   public function emergency(string $message, array $context = []): void
   {
      $this->logger->emergency($message, $context);
   }   

}

