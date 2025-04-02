<?php

declare(strict_types=1);

namespace IronFlow\Core\Logger;

use IronFlow\Support\Facades\Filesystem;

class SecurityLogger
{
   /**
    * Chemin du fichier de log
    */
   private string $logPath;

   /**
    * Constructeur
    */
   public function __construct()
   {
      $this->logPath = storage_path('logs/security.log');
   }

   /**
    * Enregistre un événement de sécurité
    */
   public function log(string $event, array $context = []): void
   {
      $timestamp = date('Y-m-d H:i:s');
      $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
      $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

      $logEntry = sprintf(
         "[%s] %s - IP: %s - User-Agent: %s - Context: %s\n",
         $timestamp,
         $event,
         $ip,
         $userAgent,
         json_encode($context, JSON_UNESCAPED_UNICODE)
      );

      Filesystem::append($this->logPath, $logEntry);
   }

   /**
    * Enregistre une tentative d'attaque CSRF
    */
   public function logCsrfAttempt(array $context = []): void
   {
      $this->log('CSRF_ATTEMPT', $context);
   }

   /**
    * Enregistre une tentative d'injection SQL
    */
   public function logSqlInjectionAttempt(array $context = []): void
   {
      $this->log('SQL_INJECTION_ATTEMPT', $context);
   }

   /**
    * Enregistre une tentative d'accès non autorisé
    */
   public function logUnauthorizedAccess(array $context = []): void
   {
      $this->log('UNAUTHORIZED_ACCESS', $context);
   }

   /**
    * Enregistre une tentative de brute force
    */
   public function logBruteForceAttempt(array $context = []): void
   {
      $this->log('BRUTE_FORCE_ATTEMPT', $context);
   }

   /**
    * Enregistre une tentative d'upload de fichier malveillant
    */
   public function logMaliciousUploadAttempt(array $context = []): void
   {
      $this->log('MALICIOUS_UPLOAD_ATTEMPT', $context);
   }

   /**
    * Nettoie les anciens logs
    */
   public function cleanOldLogs(int $days = 30): void
   {
      if (!Filesystem::exists($this->logPath)) {
         return;
      }

      $content = Filesystem::get($this->logPath);
      $lines = explode("\n", $content);
      $now = time();
      $newLines = [];

      foreach ($lines as $line) {
         if (empty($line)) {
            continue;
         }

         if (preg_match('/\[(.*?)\]/', $line, $matches)) {
            $date = strtotime($matches[1]);
            if ($now - $date < ($days * 24 * 60 * 60)) {
               $newLines[] = $line;
            }
         }
      }

      Filesystem::put($this->logPath, implode("\n", $newLines));
   }
}
