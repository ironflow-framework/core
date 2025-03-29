<?php

declare(strict_types=1);

namespace IronFlow\Channel\Contracts;

use IronFlow\Channel\Events\ChannelEvent;

/**
 * Interface de base pour les channels
 */
interface Channel
{
   /**
    * Renvoie le nom du channel
    *
    * @return string
    */
   public function getName(): string;

   /**
    * Broadcast un événement sur ce channel
    *
    * @param ChannelEvent $event L'événement à diffuser
    * @return bool
    */
   public function broadcast(ChannelEvent $event): bool;

   /**
    * S'abonne à ce channel
    *
    * @param string $userId ID de l'utilisateur
    * @return bool
    */
   public function subscribe(string $userId): bool;

   /**
    * Se désabonne de ce channel
    *
    * @param string $userId ID de l'utilisateur
    * @return bool
    */
   public function unsubscribe(string $userId): bool;

   /**
    * Vérifie si un utilisateur est autorisé à s'abonner à ce channel
    *
    * @param string $userId ID de l'utilisateur
    * @return bool
    */
   public function authorize(string $userId): bool;

   /**
    * Renvoie les abonnés de ce channel
    *
    * @return array
    */
   public function getSubscribers(): array;
}
