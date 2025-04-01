<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

use Exception;
use IronFlow\Database\Connection;

/**
 * Classe de base pour les migrations
 */
abstract class Migration
{

   public function getConnection()
   {
      return Connection::getInstance()->getConnection();
   }
   /**
    * Exécute la migration
    *
    * @return void
    */
   abstract public function up(): void;

   /**
    * Annule la migration
    *
    * @return void
    */
   abstract public function down(): void;

   /**
    * Exécute la migration dans une transaction
    *
    * @return bool Indique si la migration s'est bien exécutée
    */
   public function runUp(): bool
   {
      try {
         // $this->beginTransaction();
         $this->up();
         // $this->commitTransaction();
         return true;
      } catch (Exception $e) {
         $this->rollbackTransaction();
         throw $e;
      }
   }

   /**
    * Annule la migration dans une transaction
    *
    * @return bool Indique si la migration a bien été annulée
    */
   public function runDown(): bool
   {
      try {
         $this->beginTransaction();
         $this->down();
         $this->commitTransaction();
         return true;
      } catch (Exception $e) {
         $this->rollbackTransaction();
         throw $e;
      }
   }

   /**
    * Démarre une transaction
    *
    * @return void
    */
   protected function beginTransaction(): void
   {
      $this->getConnection()->beginTransaction();
   }

   /**
    * Valide une transaction
    *
    * @return void
    */
   protected function commitTransaction(): void
   {
      $this->getConnection()->commit();
   }


   /**
    * Annule une transaction
    *
    * @return void
    */
   protected function rollbackTransaction(): void
   {
      if ($this->getConnection()->inTransaction()) {
         $this->getConnection()->rollBack();
      }
   }

   /**
    * Exécute une requête SQL brute
    *
    * @param string $sql Requête SQL
    * @param array $params Paramètres de la requête
    * @return bool Succès de l'exécution
    */
   protected function rawQuery(string $sql, array $params = []): bool
   {
      $stmt = $this->getConnection()->prepare($sql);
      return $stmt->execute($params);
   }
}
