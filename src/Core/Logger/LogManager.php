<?php

namespace IronFlow\Core\Logger;

use Monolog\Logger;

/**
 * Gestionnaire de logs
 * Cette classe gère les logs de l'application
 */
class LogManager
{
   private static ?LogManager $instance = null;
   private Logger $logger;

   private function __construct()
   {
      $this->logger = new Logger('IronFlow');
   }

   /**
    * Obtient l'instance unique de LogManager
    *
    * @return LogManager
    */
   public static function getInstance(): LogManager
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }
      return self::$instance;
   }

   /**
    * Enregistre un message d'information
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function info(string $message, array $context = []): void
   {
      $this->logger->info($message, $context);
   }

   /**
    * Enregistre un message d'erreur
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function error(string $message, array $context = []): void
   {
      $this->logger->error($message, $context);
   }

   /**
    * Enregistre un message de warning
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function warning(string $message, array $context = []): void
   {
      $this->logger->warning($message, $context);
   }

   /**
    * Enregistre un message de débogage
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function debug(string $message, array $context = []): void
   {
      $this->logger->debug($message, $context);
   }

   /**
    * Enregistre un message critique
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function critical(string $message, array $context = []): void
   {
      $this->logger->critical($message, $context);
   }

   /**
    * Enregistre un message alert
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function alert(string $message, array $context = []): void
   {
      $this->logger->alert($message, $context);
   }

   /**
    * Enregistre un message d'urgence
    *
    * @param string $message
    * @param array $context
    * @return void
    */
   public function emergency(string $message, array $context = []): void
   {
      $this->logger->emergency($message, $context);
   }   

}

