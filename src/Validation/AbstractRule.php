<?php

declare(strict_types=1);

namespace IronFlow\Validation;

use IronFlow\Validation\Rules\RuleInterface;

/**
 * Classe abstraite pour les règles de validation
 */
abstract class AbstractRule implements RuleInterface
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
    * Paramètres de la règle
    */
   protected array $parameters = [];

   /**
    * Constructeur
    *
    * @param array $parameters Paramètres de la règle
    */
   public function __construct(array $parameters = [])
   {
      $this->parameters = $parameters;
   }

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
    * Récupère les paramètres de la règle
    *
    * @return array
    */
   public function getParameters(): array
   {
      return $this->parameters;
   }

   /**
    * Définit les paramètres de la règle
    *
    * @param array $parameters
    * @return self
    */
   public function setParameters(array $parameters): self
   {
      $this->parameters = $parameters;
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
    * Définit plusieurs attributs pour le message
    * 
    * @param array $attributes
    * @return self
    */
   public function setAttributes(array $attributes): self
   {
      $this->messageAttributes = array_merge($this->messageAttributes, $attributes);
      return $this;
   }

   /**
    * Récupère tous les attributs du message
    * 
    * @return array
    */
   public function getAttributes(): array
   {
      return $this->messageAttributes;
   }

   /**
    * Formate le message en remplaçant les attributs
    * 
    * @param string $message
    * @return string
    */
   protected function formatMessage(string $message): string
   {
      $replacements = array_merge(
         $this->messageAttributes,
         [
            ':field' => $this->messageAttributes['field'] ?? '',
            ':value' => $this->messageAttributes['value'] ?? '',
            ':parameters' => implode(', ', $this->parameters)
         ]
      );

      foreach ($replacements as $key => $value) {
         $message = str_replace(":$key", (string)$value, $message);
      }

      return $message;
   }

   /**
    * Vérifie si la règle a un message personnalisé
    * 
    * @return bool
    */
   public function hasCustomMessage(): bool
   {
      return $this->message !== null;
   }

   /**
    * Réinitialise le message personnalisé
    * 
    * @return self
    */
   public function resetMessage(): self
   {
      $this->message = null;
      return $this;
   }

   /**
    * Récupère le message par défaut
    * 
    * @return string
    */
   public function getDefaultMessage(): string
   {
      return $this->defaultMessage;
   }
}
