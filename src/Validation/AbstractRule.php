<?php

declare(strict_types=1);

namespace IronFlow\Validation;

/**
 * Classe abstraite pour les règles de validation
 */
abstract class AbstractRule implements Rule
{
   /**
    * Message d'erreur personnalisé
    */
   protected ?string $message = null;

   /**
    * Message d'erreur par défaut
    */
   protected string $defaultMessage = 'La validation a échoué';

   /**
    * Attributs pour le message d'erreur
    */
   protected array $messageAttributes = [];

   /**
    * Récupère le message d'erreur
    *
    * @return string
    */
   public function getMessage(): string
   {
      return $this->formatMessage($this->message ?? $this->defaultMessage);
   }

   /**
    * Définit le message d'erreur
    *
    * @param string $message
    * @return self
    */
   public function setMessage(string $message): self
   {
      $this->message = $message;
      return $this;
   }

   /**
    * Définit un attribut pour le message
    * 
    * @param string $key
    * @param string $value
    * @return self
    */
   public function setAttribute(string $key, string $value): self
   {
      $this->messageAttributes[$key] = $value;
      return $this;
   }

   /**
    * Formate le message en remplaçant les attributs
    * 
    * @param string $message
    * @return string
    */
   protected function formatMessage(string $message): string
   {
      foreach ($this->messageAttributes as $key => $value) {
         $message = str_replace(":$key", (string)$value, $message);
      }

      return $message;
   }
}
