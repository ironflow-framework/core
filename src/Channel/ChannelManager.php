<?php

declare(strict_types=1);

namespace IronFlow\Channel;

use IronFlow\Channel\Contracts\Channel;
use IronFlow\Channel\Events\ChannelEvent;
use IronFlow\Channel\Providers\WebSocketProvider;
use IronFlow\Channel\Providers\PusherProvider;
use IronFlow\Channel\Providers\SocketIOProvider;
use IronFlow\Support\Facades\Config;
use InvalidArgumentException;

/**
 * Gestionnaire principal des channels
 */
class ChannelManager
{
   /**
    * Liste des channels
    */
   protected array $channels = [];

   /**
    * Provider actuel pour la diffusion des événements
    */
   protected object $provider;

   /**
    * Liste des providers disponibles
    */
   protected array $providers = [];

   /**
    * Constructeur
    */
   public function __construct()
   {
      $this->registerDefaultProviders();

      // Provider par défaut
      $default = Config::get('channel.default', 'websocket');
      $this->setProvider($default);
   }

   /**
    * Enregistre les providers par défaut
    */
   protected function registerDefaultProviders(): void
   {
      $this->providers = [
         'websocket' => WebSocketProvider::class,
         'pusher' => PusherProvider::class,
         'socketio' => SocketIOProvider::class
      ];
   }

   /**
    * Définit le provider à utiliser
    *
    * @param string $name Nom du provider
    * @return self
    */
   public function setProvider(string $name): self
   {
      if (!isset($this->providers[$name])) {
         throw new InvalidArgumentException("Le provider de channel '{$name}' n'existe pas.");
      }

      $config = Config::get("channel.providers.{$name}", []);
      $providerClass = $this->providers[$name];

      $this->provider = new $providerClass($config);

      return $this;
   }

   /**
    * Crée un nouveau channel
    *
    * @param string $name Nom du channel
    * @param array $options Options du channel
    * @return Channel
    */
   public function createChannel(string $name, array $options = []): Channel
   {
      if (isset($this->channels[$name])) {
         return $this->channels[$name];
      }

      $channel = $this->provider->createChannel($name, $options);
      $this->channels[$name] = $channel;

      return $channel;
   }

   /**
    * Récupère un channel existant
    *
    * @param string $name Nom du channel
    * @return Channel|null
    */
   public function getChannel(string $name): ?Channel
   {
      return $this->channels[$name] ?? null;
   }

   /**
    * Vérifie si un channel existe
    *
    * @param string $name Nom du channel
    * @return bool
    */
   public function hasChannel(string $name): bool
   {
      return isset($this->channels[$name]);
   }

   /**
    * Diffuse un événement sur un channel
    *
    * @param string $channelName Nom du channel
    * @param ChannelEvent $event Événement à diffuser
    * @return bool
    */
   public function broadcast(string $channelName, ChannelEvent $event): bool
   {
      if (!$this->hasChannel($channelName)) {
         $this->createChannel($channelName);
      }

      return $this->channels[$channelName]->broadcast($event);
   }

   /**
    * Diffuse un événement sur plusieurs channels
    *
    * @param array $channelNames Noms des channels
    * @param ChannelEvent $event Événement à diffuser
    * @return array
    */
   public function broadcastToMany(array $channelNames, ChannelEvent $event): array
   {
      $results = [];

      foreach ($channelNames as $channelName) {
         $results[$channelName] = $this->broadcast($channelName, $event);
      }

      return $results;
   }

   /**
    * S'abonne à un channel
    *
    * @param string $channelName Nom du channel
    * @param string $userId ID de l'utilisateur
    * @return bool
    */
   public function subscribe(string $channelName, string $userId): bool
   {
      if (!$this->hasChannel($channelName)) {
         $this->createChannel($channelName);
      }

      return $this->channels[$channelName]->subscribe($userId);
   }

   /**
    * Se désabonne d'un channel
    *
    * @param string $channelName Nom du channel
    * @param string $userId ID de l'utilisateur
    * @return bool
    */
   public function unsubscribe(string $channelName, string $userId): bool
   {
      if (!$this->hasChannel($channelName)) {
         return false;
      }

      return $this->channels[$channelName]->unsubscribe($userId);
   }
}
