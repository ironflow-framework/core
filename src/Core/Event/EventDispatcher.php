<?php

declare(strict_types=1);

namespace IronFlow\Core\Event;

/**
 * Gestionnaire d'événements
 * 
 * Cette classe gère la distribution des événements dans l'application.
 * Elle permet d'enregistrer des écouteurs d'événements et de les déclencher.
 */
class EventDispatcher
{
   /**
    * Les écouteurs d'événements enregistrés
    * 
    * @var array<string, array<callable>>
    */
   private array $listeners = [];

   /**
    * Ajoute un écouteur d'événement
    * 
    * @param string $eventName Le nom de l'événement
    * @param callable $listener L'écouteur à ajouter
    * @param int $priority La priorité de l'écouteur (plus le nombre est élevé, plus la priorité est haute)
    */
   public function addListener(string $eventName, callable $listener, int $priority = 0): void
   {
      if (!isset($this->listeners[$eventName])) {
         $this->listeners[$eventName] = [];
      }

      $this->listeners[$eventName][] = [
         'listener' => $listener,
         'priority' => $priority
      ];
   }

   /**
    * Déclenche un événement
    * 
    * @param string $eventName Le nom de l'événement
    * @param Event $event L'événement à distribuer
    */
   public function dispatch(string $eventName, Event $event): void
   {
      if (!isset($this->listeners[$eventName])) {
         return;
      }

      // Trie les écouteurs par priorité
      usort($this->listeners[$eventName], function ($a, $b) {
         return $b['priority'] - $a['priority'];
      });

      foreach ($this->listeners[$eventName] as $listener) {
         $listener['listener']($event);
      }
   }

   /**
    * Supprime un écouteur d'événement
    * 
    * @param string $eventName Le nom de l'événement
    * @param callable $listener L'écouteur à supprimer
    */
   public function removeListener(string $eventName, callable $listener): void
   {
      if (!isset($this->listeners[$eventName])) {
         return;
      }

      foreach ($this->listeners[$eventName] as $key => $registered) {
         if ($registered['listener'] === $listener) {
            unset($this->listeners[$eventName][$key]);
            break;
         }
      }
   }

   /**
    * Vérifie si un événement a des écouteurs
    * 
    * @param string $eventName Le nom de l'événement
    * @return bool True si l'événement a des écouteurs
    */
   public function hasListeners(string $eventName): bool
   {
      return isset($this->listeners[$eventName]) && !empty($this->listeners[$eventName]);
   }

   /**
    * Récupère tous les écouteurs d'un événement
    * 
    * @param string $eventName
    * @return array<callable>
    */
   public function getListeners(string $eventName): array
   {
      return $this->listeners[$eventName] ?? [];
   }
}
