<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron;

use IronFlow\Database\Connection;

/**
 * Gestionnaire principal de la base de données Iron ORM
 * 
 * Cette classe est responsable de gérer les connexions à la base de données
 * et fournir une interface unique pour accéder aux fonctionnalités de l'ORM.
 */
class IronManager
{
   /**
    * Instance de la connexion à la base de données
    *
    * @var Connection|null
    */
   protected ?Connection $connection = null;

   /**
    * Constructeur
    */
   public function __construct()
   {
      // Initialisation du gestionnaire de base de données
   }

   /**
    * Retourne l'instance de connexion à la base de données
    *
    * @return Connection
    */
   public function connection(): Connection
   {
      if ($this->connection === null) {
         $this->connection = Connection::getInstance();
      }

      return $this->connection;
   }

   /**
    * Exécute une requête SQL brute
    *
    * @param string $query Requête SQL
    * @param array $bindings Paramètres à lier
    * @return mixed
    */
   public function raw(string $query, array $bindings = [])
   {
      return $this->connection()->getConnection()->prepare($query)->execute($bindings);
   }

   /**
    * Démarre une transaction
    *
    * @return bool
    */
   public function beginTransaction(): bool
   {
      return $this->connection()->getConnection()->beginTransaction();
   }

   /**
    * Valide une transaction
    *
    * @return bool
    */
   public function commit(): bool
   {
      return $this->connection()->getConnection()->commit();
   }

   /**
    * Annule une transaction
    *
    * @return bool
    */
   public function rollBack(): bool
   {
      return $this->connection()->getConnection()->rollBack();
   }
}
