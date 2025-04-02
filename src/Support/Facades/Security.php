<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Core\Logger\SecurityLogger;

class Security extends Facade
{
   /**
    * Récupère l'instance du SecurityLogger
    */
   protected static function getFacadeInstance(): SecurityLogger
   {
      return new SecurityLogger();
   }

   /**
    * Enregistre un événement de sécurité
    */
   public static function log(string $event, array $context = []): void
   {
      static::getFacadeInstance()->log($event, $context);
   }

   /**
    * Enregistre une tentative d'attaque CSRF
    */
   public static function logCsrfAttempt(array $context = []): void
   {
      static::getFacadeInstance()->logCsrfAttempt($context);
   }

   /**
    * Enregistre une tentative d'injection SQL
    */
   public static function logSqlInjectionAttempt(array $context = []): void
   {
      static::getFacadeInstance()->logSqlInjectionAttempt($context);
   }

   /**
    * Enregistre une tentative d'accès non autorisé
    */
   public static function logUnauthorizedAccess(array $context = []): void
   {
      static::getFacadeInstance()->logUnauthorizedAccess($context);
   }

   /**
    * Enregistre une tentative de brute force
    */
   public static function logBruteForceAttempt(array $context = []): void
   {
      static::getFacadeInstance()->logBruteForceAttempt($context);
   }

   /**
    * Enregistre une tentative d'upload de fichier malveillant
    */
   public static function logMaliciousUploadAttempt(array $context = []): void
   {
      static::getFacadeInstance()->logMaliciousUploadAttempt($context);
   }

   /**
    * Nettoie les anciens logs
    */
   public static function cleanOldLogs(int $days = 30): void
   {
      static::getFacadeInstance()->cleanOldLogs($days);
   }
}
