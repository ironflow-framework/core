<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeder;

use PDO;
use IronFlow\Database\Connection;

/**
 * Classe de base pour les seeders de base de données
 * 
 * Cette classe fournit une base pour la création de seeders qui permettent
 * de remplir la base de données avec des données de test ou initiales.
 * 
 * @package IronFlow\Database\Seeder
 * @author IronFlow Team
 * @version 1.0.0
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
    * Liste des seeders déjà exécutés
    *
    * @var array<string>
    */
   protected static array $executedSeeders = [];

   /**
    * Constructeur
    *
    * @param PDO|Connection $connection Connexion à la base de données
    * @throws \InvalidArgumentException Si le paramètre de connexion n'est pas valide
    */
   public function __construct($connection)
   {
      if ($connection instanceof Connection) {
         $this->connection = $connection->getConnection();
      } elseif ($connection instanceof PDO) {
         $this->connection = $connection;
      } else {
         throw new \InvalidArgumentException("Le paramètre de connexion doit être une instance de PDO ou Connection");
      }
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
    * @param array<string, mixed> $options Options supplémentaires pour le seeder
    * @return void
    * @throws \InvalidArgumentException Si la classe de seeder n'existe pas
    */
   protected function call(string $class, array $options = []): void
   {
      if (!class_exists($class)) {
         throw new \InvalidArgumentException("La classe de seeder '$class' n'existe pas.");
      }

      // Évite l'exécution multiple du même seeder
      if (in_array($class, self::$executedSeeders)) {
         return;
      }

      $seeder = new $class($this->connection);
      self::$executedSeeders[] = $class;
      $seeder->run();
   }

   /**
    * Insère des données dans une table
    *
    * @param string $table Nom de la table
    * @param array<string, mixed> $data Données à insérer
    * @return bool Succès de l'insertion
    * @throws \PDOException Si l'insertion échoue
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
    * @param array<array<string, mixed>> $records Tableau d'enregistrements à insérer
    * @return bool Succès de l'insertion
    * @throws \PDOException Si l'insertion échoue
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
    * @throws \PDOException Si l'opération échoue
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
    * @param array<string, mixed> $params Paramètres de la requête
    * @return bool Succès de l'exécution
    * @throws \PDOException Si l'exécution échoue
    */
   protected function rawQuery(string $sql, array $params = []): bool
   {
      $stmt = $this->connection->prepare($sql);
      return $stmt->execute($params);
   }

   /**
    * Vérifie si une table existe
    *
    * @param string $table Nom de la table
    * @return bool
    */
   protected function tableExists(string $table): bool
   {
      $driver = $this->connection->getAttribute(PDO::ATTR_DRIVER_NAME);

      switch ($driver) {
         case 'mysql':
            $result = $this->rawQuery("SHOW TABLES LIKE ?", [$table]);
            return $result && $this->connection->query("SELECT FOUND_ROWS()")->fetchColumn() > 0;

         case 'sqlite':
            $result = $this->rawQuery("SELECT name FROM sqlite_master WHERE type='table' AND name=?", [$table]);
            return $result && $this->connection->query("SELECT changes()")->fetchColumn() > 0;

         case 'pgsql':
            $result = $this->rawQuery("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = ?)", [$table]);
            return $result && $this->connection->query("SELECT row_count()")->fetchColumn() > 0;

         default:
            return false;
      }
   }

   /**
    * Réinitialise la liste des seeders exécutés
    *
    * @return void
    */
   public static function resetExecutedSeeders(): void
   {
      self::$executedSeeders = [];
   }
}
