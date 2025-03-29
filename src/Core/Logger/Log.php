<?php

namespace IronFlow\Logger;

use Monolog\Logger;


class Log
{
   private static ?Log $instance = null;
   private Logger $logger;

   private function __construct()
   {
      $this->logger = new Logger('IronFlow');
   }

   public static function getInstance(): Log
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
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

