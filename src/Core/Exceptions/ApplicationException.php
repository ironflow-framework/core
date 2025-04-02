<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

/**
 * Classe de base pour les exceptions de l'application
 * 
 * Cette classe définit l'interface standard pour toutes les exceptions
 * personnalisées dans l'application. Elle permet de gérer les erreurs
 * de manière cohérente et de fournir des informations contextuelles.
 */
abstract class ApplicationException extends \Exception
{
   /**
    * Les données contextuelles associées à l'exception
    * 
    * @var array<string, mixed>
    */
   protected array $context = [];

   /**
    * Crée une nouvelle instance de l'exception
    * 
    * @param string $message Le message d'erreur
    * @param array<string, mixed> $context Les données contextuelles
    * @param int $code Le code d'erreur
    * @param \Throwable|null $previous L'exception précédente
    */
   public function __construct(
      string $message = "",
      array $context = [],
      int $code = 0,
      ?\Throwable $previous = null
   ) {
      parent::__construct($message, $code, $previous);
      $this->context = $context;
   }

   /**
    * Récupère les données contextuelles associées à l'exception
    * 
    * @return array<string, mixed> Les données contextuelles
    */
   public function getContext(): array
   {
      return $this->context;
   }

   /**
    * Récupère une donnée contextuelle spécifique
    * 
    * @param string $key La clé de la donnée
    * @param mixed $default La valeur par défaut si la clé n'existe pas
    * @return mixed La valeur associée à la clé
    */
   public function getContextValue(string $key, mixed $default = null): mixed
   {
      return $this->context[$key] ?? $default;
   }

   /**
    * Convertit l'exception en tableau pour le débogage
    * 
    * @return array<string, mixed> Les données de l'exception
    */
   public function toArray(): array
   {
      return [
         'message' => $this->getMessage(),
         'code' => $this->getCode(),
         'file' => $this->getFile(),
         'line' => $this->getLine(),
         'context' => $this->context,
         'trace' => $this->getTrace()
      ];
   }
}
