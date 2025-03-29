<?php

declare(strict_types=1);

namespace IronFlow\Payment\Exceptions;

/**
 * Exception spécifique au système de paiement
 */
class PaymentException extends \Exception
{
   /**
    * Données de contexte de l'exception
    */
   protected array $context = [];

   /**
    * Constructeur
    */
   public function __construct(string $message = "", int $code = 0, \Throwable $previous = null, array $context = [])
   {
      parent::__construct($message, $code, $previous);
      $this->context = $context;
   }

   /**
    * Récupère les données de contexte
    */
   public function getContext(): array
   {
      return $this->context;
   }

   /**
    * Ajoute une donnée de contexte
    */
   public function addContext(string $key, mixed $value): self
   {
      $this->context[$key] = $value;
      return $this;
   }

   /**
    * Crée une exception à partir d'une réponse HTTP
    */
   public static function fromHttpResponse(int $statusCode, string $message, array $responseData = []): self
   {
      return new self(
         "Erreur HTTP $statusCode: $message",
         $statusCode,
         null,
         ['response' => $responseData]
      );
   }

   /**
    * Crée une exception à partir d'une erreur d'API
    */
   public static function fromApiError(string $errorCode, string $message, array $details = []): self
   {
      return new self(
         "Erreur d'API ($errorCode): $message",
         0,
         null,
         ['api_error_code' => $errorCode, 'details' => $details]
      );
   }

   /**
    * Crée une exception pour un provider non configuré
    */
   public static function providerNotConfigured(string $providerName): self
   {
      return new self(
         "Le provider de paiement '$providerName' n'est pas correctement configuré",
         0,
         null,
         ['provider' => $providerName]
      );
   }

   /**
    * Crée une exception pour une action non supportée
    */
   public static function unsupportedAction(string $action, string $providerName): self
   {
      return new self(
         "L'action '$action' n'est pas supportée par le provider de paiement '$providerName'",
         0,
         null,
         ['action' => $action, 'provider' => $providerName]
      );
   }
}
