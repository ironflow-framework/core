<?php

declare(strict_types=1);

namespace IronFlow\Core\Event;

/**
 * Classe de base pour les événements
 * 
 * Cette classe abstraite définit l'interface standard pour tous les événements
 * dans l'application. Elle permet de créer des événements personnalisés avec
 * des données associées.
 */
abstract class Event
{
   /**
    * Les données associées à l'événement
    * 
    * @var array<string, mixed>
    */
   protected array $data = [];

   /**
    * Crée une nouvelle instance de l'événement
    * 
    * @param array<string, mixed> $data Les données associées à l'événement
    */
   public function __construct(array $data = [])
   {
      $this->data = $data;
   }

   /**
    * Récupère une donnée associée à l'événement
    * 
    * @param string $key La clé de la donnée
    * @param mixed $default La valeur par défaut si la clé n'existe pas
    * @return mixed La valeur associée à la clé
    */
   public function get(string $key, mixed $default = null): mixed
   {
      return $this->data[$key] ?? $default;
   }

   /**
    * Définit une donnée associée à l'événement
    * 
    * @param string $key La clé de la donnée
    * @param mixed $value La valeur à associer
    */
   public function set(string $key, mixed $value): void
   {
      $this->data[$key] = $value;
   }

   /**
    * Récupère toutes les données associées à l'événement
    * 
    * @return array<string, mixed> Les données de l'événement
    */
   public function getData(): array
   {
      return $this->data;
   }

   /**
    * @var bool
    */
   private bool $propagationStopped = false;

   /**
    * Arrête la propagation de l'événement
    * 
    * @return void
    */
   public function stopPropagation(): void
   {
      $this->propagationStopped = true;
   }

   /**
    * Vérifie si la propagation est arrêtée
    * 
    * @return bool
    */
   public function isPropagationStopped(): bool
   {
      return $this->propagationStopped;
   }
}
