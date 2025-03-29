<?php

declare(strict_types=1);

namespace IronFlow\Channel\Contracts;

use IronFlow\Channel\Events\ChannelEvent;

/**
 * Interface pour les providers de channel
 */
interface ChannelProvider
{
   /**
    * Crée un nouveau channel
    *
    * @param string $name Nom du channel
    * @param array $options Options du channel
    * @return Channel
    */
   public function createChannel(string $name, array $options = []): Channel;

   /**
    * Connecte au service de diffusion
    *
    * @return bool
    */
   public function connect(): bool;

   /**
    * Déconnecte du service de diffusion
    *
    * @return bool
    */
   public function disconnect(): bool;

   /**
    * Vérifie si la connexion est établie
    *
    * @return bool
    */
   public function isConnected(): bool;

   /**
    * Diffuse un événement sur un channel
    *
    * @param string $channelName Nom du channel
    * @param ChannelEvent $event Événement à diffuser
    * @return bool
    */
   public function broadcast(string $channelName, ChannelEvent $event): bool;
}
