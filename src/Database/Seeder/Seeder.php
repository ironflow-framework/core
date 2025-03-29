<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeder;

use PDO;

/**
 * Classe de base pour les seeders de base de données
 */
abstract class Seeder
{
   /**
    * Instance de la connexion à la base de données
    *
    * @var PDO
    */
   protected PDO $connection;

   /**
    * Constructeur
    *
    * @param PDO $connection Connexion à la base de données
    */
   public function __construct(PDO $connection)
   {
      $this->connection = $connection;
   }

   /**
    * Exécute les opérations de seeding
    *
    * @return void
    */
   abstract public function run(): void;

   /**
    * Exécute un autre seeder
    *
    * @param string $class Nom de la classe du seeder
    * @return void
    */
   protected function call(string $class): void
   {
      if (!class_exists($class)) {
         throw new \InvalidArgumentException("La classe de seeder '$class' n'existe pas.");
      }

      $seeder = new $class($this->connection);
      $seeder->run();
   }

   /**
    * Insère des données dans une table
    *
    * @param string $table Nom de la table
    * @param array $data Données à insérer
    * @return bool Succès de l'insertion
    */
   protected function insert(string $table, array $data): bool
   {
      $columns = implode(', ', array_keys($data));
      $placeholders = implode(', ', array_fill(0, count($data), '?'));

      $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
      $stmt = $this->connection->prepare($sql);

      return $stmt->execute(array_values($data));
   }

   /**
    * Insère plusieurs enregistrements dans une table
    *
    * @param string $table Nom de la table
    * @param array $records Tableau d'enregistrements à insérer
    * @return bool Succès de l'insertion
    */
   protected function insertMany(string $table, array $records): bool
   {
      if (empty($records)) {
         return true;
      }

      $this->connection->beginTransaction();

      try {
         foreach ($records as $record) {
            $this->insert($table, $record);
         }

         $this->connection->commit();
         return true;
      } catch (\Exception $e) {
         $this->connection->rollBack();
         throw $e;
      }
   }

   /**
    * Truncate une table (supprime toutes les données)
    *
    * @param string $table Nom de la table
    * @param bool $cascade Supprimer en cascade
    * @return bool Succès de l'opération
    */
   protected function truncate(string $table, bool $cascade = false): bool
   {
      $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

      switch ($driver) {
         case 'mysql':
            // MySQL a besoin de désactiver les clés étrangères pour TRUNCATE
            $this->connection->exec('SET FOREIGN_KEY_CHECKS=0');
            $result = $this->connection->exec("TRUNCATE TABLE $table") !== false;
            $this->connection->exec('SET FOREIGN_KEY_CHECKS=1');
            return $result;

         case 'sqlite':
            // SQLite ne supporte pas TRUNCATE, utilise DELETE FROM
            return $this->connection->exec("DELETE FROM $table") !== false;

         case 'pgsql':
            // PostgreSQL supporte TRUNCATE avec CASCADE
            $cascadeOption = $cascade ? ' CASCADE' : '';
            return $this->connection->exec("TRUNCATE TABLE $table$cascadeOption") !== false;

         default:
            return $this->connection->exec("DELETE FROM $table") !== false;
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
      $stmt = $this->connection->prepare($sql);
      return $stmt->execute($params);
   }
}
