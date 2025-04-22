<?php

declare(strict_types=1);

namespace IronFlow\Channel\Events;

use IronFlow\Core\Event\Event;

/**
 * Classe de base pour tous les événements des channels
 */
class ChannelEvent extends Event
{
   /**
    * Utilisateur ayant déclenché l'événement
    */
   protected ?string $userId;

   /**
    * Timestamp de l'événement
    */
   protected int $timestamp;

   /**
    * Constructeur
    *
    * @param string $name Nom de l'événement
    * @param array $data Données de l'événement
    * @param string|null $userId ID de l'utilisateur
    */
   public function __construct(string $name, array $data = [], ?string $userId = null)
   {
      parent::__construct($name, $data);
      $this->userId = $userId;
      $this->timestamp = time();
   }

   /**
    * Renvoie l'ID de l'utilisateur
    *
    * @return string|null
    */
   public function getUserId(): ?string
   {
      return $this->userId;
   }

   /**
    * Renvoie le timestamp de l'événement
    *
    * @return int
    */
   public function getTimestamp(): int
   {
      return $this->timestamp;
   }

   /**
    * Convertit l'événement en tableau
    *
    * @return array
    */
   public function toArray(): array
   {
      return [
         'name' => $this->getName(),
         'data' => $this->getData(),
         'user_id' => $this->userId,
         'timestamp' => $this->timestamp
      ];
   }

   /**
    * Convertit l'événement en JSON
    *
    * @return string
    */
   public function toJson(): string
   {
      return json_encode($this->toArray());
   }
}
