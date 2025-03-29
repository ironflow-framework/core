<?php

declare(strict_types=1);

namespace IronFlow\Channel\Models;

use IronFlow\Channel\Contracts\Channel;
use IronFlow\Channel\Events\ChannelEvent;
use IronFlow\Support\Facades\Log;

/**
 * Implémentation d'un channel WebSocket
 */
class WebSocketChannel implements Channel
{
   /**
    * Nom du channel
    */
   protected string $name;

   /**
    * Configuration du channel
    */
   protected array $config;

   /**
    * Liste des abonnés au channel
    */
   protected array $subscribers = [];

   /**
    * Constructeur
    *
    * @param string $name Nom du channel
    * @param array $config Configuration du channel
    */
   public function __construct(string $name, array $config = [])
   {
      $this->name = $name;
      $this->config = $config;
   }

   /**
    * {@inheritdoc}
    */
   public function getName(): string
   {
      return $this->name;
   }

   /**
    * {@inheritdoc}
    */
   public function broadcast(ChannelEvent $event): bool
   {
      try {
         $payload = $event->toJson();

         // En production, envoyez réellement le message via WebSocket
         Log::info("Broadcasting event '{$event->getName()}' to channel '{$this->name}'", [
            'channel' => $this->name,
            'event' => $event->getName(),
            'subscribers' => count($this->subscribers)
         ]);

         // Diffusion simulée aux abonnés
         foreach ($this->subscribers as $userId) {
            Log::debug("Envoi de l'événement à l'utilisateur {$userId}");
         }

         return true;
      } catch (\Exception $e) {
         Log::error("Erreur de broadcast: " . $e->getMessage());
         return false;
      }
   }

   /**
    * {@inheritdoc}
    */
   public function subscribe(string $userId): bool
   {
      if (!$this->authorize($userId)) {
         return false;
      }

      if (!in_array($userId, $this->subscribers)) {
         $this->subscribers[] = $userId;
         Log::info("Utilisateur {$userId} abonné au channel '{$this->name}'");
      }

      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function unsubscribe(string $userId): bool
   {
      $key = array_search($userId, $this->subscribers);

      if ($key !== false) {
         unset($this->subscribers[$key]);
         $this->subscribers = array_values($this->subscribers); // Réindexer le tableau
         Log::info("Utilisateur {$userId} désabonné du channel '{$this->name}'");
         return true;
      }

      return false;
   }

   /**
    * {@inheritdoc}
    */
   public function authorize(string $userId): bool
   {
      // Par défaut, tous les utilisateurs sont autorisés.
      // Pour les channels privés, surcharger cette méthode
      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function getSubscribers(): array
   {
      return $this->subscribers;
   }
}
