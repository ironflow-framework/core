<?php

declare(strict_types=1);

namespace IronFlow\Database\Seeders;

use PDO;
use Database\Seeders\DatabaseSeeder;
use IronFlow\Database\Connection;

/**
 * Gestionnaire des seeders de base de données
 * 
 * Cette classe permet d'exécuter les seeders de base de données
 * de manière centralisée. Elle gère l'ordre d'exécution des seeders
 * et assure que chaque seeder n'est exécuté qu'une seule fois.
 * 
 * @package IronFlow\Database\Seeder
 * @author Aure Dulvresse
 * @version 1.0.0
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
    * Liste des seeders exécutés
    * 
    * @var array<string>
    */
   protected array $executedSeeders = [];

   /**
    * Constructeur
    * 
    * @param PDO|Connection|null $connection Instance de la connexion
    * @throws \InvalidArgumentException Si la connexion n'est pas valide
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
    * Cette méthode charge et exécute le seeder principal (DatabaseSeeder)
    * qui est responsable d'appeler tous les autres seeders dans le bon ordre.
    * 
    * @return void
    * @throws \RuntimeException Si le seeder principal n'existe pas
    */
   public function run(): void
   {
      $seederPath = $this->getSeedersPath() . '/DatabaseSeeder.php';

      if (!file_exists($seederPath)) {
         throw new \RuntimeException("Le seeder principal n'existe pas: {$seederPath}");
      }

      require_once $seederPath;

      if (!class_exists("Database\\Seeders\\DatabaseSeeder")) {
         throw new \RuntimeException("La classe DatabaseSeeder n'existe pas");
      }

      $seeder = new DatabaseSeeder($this->connection);
      $seeder->run();
   }

   /**
    * Exécute un seeder spécifique
    * 
    * @param string $seeder Nom du seeder (sans le suffixe "Seeder")
    * @param array<string, mixed> $options Options supplémentaires pour le seeder
    * @return void
    * @throws \RuntimeException Si le seeder n'existe pas
    */
   public function runSpecific(string $seeder, array $options = []): void
   {
      $class = $this->getSeederClass($seeder);

      if (!class_exists($class)) {
         throw new \RuntimeException("La classe de seeder '{$class}' n'existe pas");
      }

      // Vérifie si le seeder a déjà été exécuté
      if (in_array($class, $this->executedSeeders)) {
         return;
      }

      $instance = new $class($this->connection);
      $instance->run();
      $this->executedSeeders[] = $class;
   }

   /**
    * Récupère le nom complet de la classe d'un seeder
    * 
    * @param string $seeder Nom du seeder (sans le suffixe "Seeder")
    * @return string
    * @throws \RuntimeException Si le fichier de seeder n'existe pas
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
    * @return array<string>
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

   /**
    * Réinitialise la liste des seeders exécutés
    * 
    * @return void
    */
   public function resetExecutedSeeders(): void
   {
      $this->executedSeeders = [];
   }

   /**
    * Vérifie si un seeder a été exécuté
    * 
    * @param string $seeder Nom du seeder
    * @return bool
    */
   public function hasSeederBeenExecuted(string $seeder): bool
   {
      return in_array($this->getSeederClass($seeder), $this->executedSeeders);
   }

   /**
    * Exécute plusieurs seeders dans l'ordre spécifié
    * 
    * @param array<string> $seeders Liste des seeders à exécuter
    * @return void
    */
   public function runMultiple(array $seeders): void
   {
      foreach ($seeders as $seeder) {
         $this->runSpecific($seeder);
      }
   }
}
