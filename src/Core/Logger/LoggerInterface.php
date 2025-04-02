<?php

declare(strict_types=1);

namespace IronFlow\Core\Logger;

/**
 * Interface LoggerInterface
 * 
 * Définit le contrat pour le système de logging
 */
interface LoggerInterface
{
   /**
    * Log un message d'urgence
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function emergency(string $message, array $context = []): void;

   /**
    * Log un message d'alerte
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function alert(string $message, array $context = []): void;

   /**
    * Log un message critique
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function critical(string $message, array $context = []): void;

   /**
    * Log un message d'erreur
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function error(string $message, array $context = []): void;

   /**
    * Log un message d'avertissement
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function warning(string $message, array $context = []): void;

   /**
    * Log un message de notice
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function notice(string $message, array $context = []): void;

   /**
    * Log un message d'information
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function info(string $message, array $context = []): void;

   /**
    * Log un message de debug
    * 
    * @param string $message
    * @param array $context
    * @return void
    */
   public function debug(string $message, array $context = []): void;
}
