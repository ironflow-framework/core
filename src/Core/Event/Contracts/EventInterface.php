<?php

declare(strict_types=1);

namespace IronFlow\Core\Event\Contracts;

/**
 * Interface pour les événements
 */
interface EventInterface
{
   /**
    * Récupère le nom de l'événement
    *
    * @return string
    */
   public function getName(): string;

   /**
    * Récupère les données de l'événement
    *
    * @return array<string, mixed>
    */
   public function getData(): array;

   /**
    * Récupère une donnée spécifique
    *
    * @param string $key
    * @param mixed $default
    * @return mixed
    */
   public function get(string $key, mixed $default = null): mixed;

   /**
    * Définit une donnée
    *
    * @param string $key
    * @param mixed $value
    * @return void
    */
   public function set(string $key, mixed $value): void;

   /**
    * Arrête la propagation de l'événement
    *
    * @return void
    */
   public function stopPropagation(): void;

   /**
    * Vérifie si la propagation est arrêtée
    *
    * @return bool
    */
   public function isPropagationStopped(): bool;
}
