<?php

declare(strict_types=1);

namespace IronFlow\Channel\Events;

/**
 * Classe de base pour tous les événements des channels
 */
class ChannelEvent
{
   /**
    * Nom de l'événement
    */
   protected string $name;

   /**
    * Données associées à l'événement
    */
   protected array $data;

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
      $this->name = $name;
      $this->data = $data;
      $this->userId = $userId;
      $this->timestamp = time();
   }

   /**
    * Renvoie le nom de l'événement
    *
    * @return string
    */
   public function getName(): string
   {
      return $this->name;
   }

   /**
    * Renvoie les données de l'événement
    *
    * @return array
    */
   public function getData(): array
   {
      return $this->data;
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
         'name' => $this->name,
         'data' => $this->data,
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
