<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeder;

use PDO;
use Database\Seeder\DatabaseSeeder;
use IronFlow\Database\Connection;

/**
 * Gestionnaire des seeders de base de données
 * 
 * Cette classe permet d'exécuter les seeders de base de données
 * de manière centralisée.
 */
class SeederManager
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
    * @param PDO|Connection|null $connection Instance de la connexion
    */
   public function __construct($connection = null)
   {
      if ($connection instanceof Connection) {
         $this->connection = $connection->getInstance()->getConnection();
      } elseif ($connection instanceof PDO) {
         $this->connection = $connection;
      } else {
         $this->connection = Connection::getInstance()->getConnection();
      }
   }

   /**
    * Exécute tous les seeders
    * 
    * @return void
    */
   public function run(): void
   {
      $seederPath = $this->getSeedersPath() . '/DatabaseSeeder.php';

      if (!file_exists($seederPath)) {
         throw new \RuntimeException("Le seeder principal n'existe pas: {$seederPath}");
      }

      require_once $seederPath;

      if (!class_exists("Database\\Seeder\\DatabaseSeeder")) {
         throw new \RuntimeException("La classe DatabaseSeeder n'existe pas");
      }

      $seeder = new DatabaseSeeder($this->connection);
      $seeder->run();
   }

   /**
    * Exécute un seeder spécifique
    * 
    * @param string $seeder Nom du seeder (sans le suffixe "Seeder")
    * @return void
    */
   public function runSpecific(string $seeder): void
   {
      $class = $this->getSeederClass($seeder);

      if (!class_exists($class)) {
         throw new \RuntimeException("La classe de seeder '{$class}' n'existe pas");
      }

      $instance = new $class($this->connection);
      $instance->run();
   }

   /**
    * Récupère le nom complet de la classe d'un seeder
    * 
    * @param string $seeder Nom du seeder (sans le suffixe "Seeder")
    * @return string
    */
   protected function getSeederClass(string $seeder): string
   {
      $file = $this->getSeedersPath() . '/' . $seeder . '.php';

      if (!file_exists($file)) {
         throw new \RuntimeException("Le fichier de seeder n'existe pas: {$file}");
      }

      require_once $file;

      $class = str_replace(' ', '', ucwords(str_replace('_', ' ', $seeder))) . 'Seeder';
      return "Database\\Seeder\\{$class}";
   }

   /**
    * Récupère le chemin vers le répertoire des seeders
    * 
    * @return string
    */
   protected function getSeedersPath(): string
   {
      return database_path('seeders');
   }

   /**
    * Liste tous les seeders disponibles
    * 
    * @return array
    */
   public function listSeeders(): array
   {
      $path = $this->getSeedersPath();
      $files = glob("{$path}/*.php");
      $seeders = [];

      foreach ($files as $file) {
         $basename = basename($file, '.php');
         // Exclure DatabaseSeeder car c'est le point d'entrée
         if ($basename !== 'DatabaseSeeder') {
            $seeders[] = $basename;
         }
      }

      sort($seeders);
      return $seeders;
   }
}
