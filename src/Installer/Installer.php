<?php

namespace IronFlow\Installer;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class Installer
{
   /**
    * Exécute les tâches post-installation
    *
    * @return void
    */
   public static function postInstall(): void
   {
      $input = new InputInterface();
      $output = new OutputInterface();
      $io = new SymfonyStyle($input, $output);

      $io->title('Installation d\'IronFlow Framework');

      // Vérification des prérequis
      self::checkRequirements($io);

      // Configuration de l'environnement
      self::setupEnvironment($io);

      // Installation des dépendances
      self::installDependencies($io);

      $io->success('Installation terminée avec succès !');
   }

   /**
    * Exécute les tâches post-mise à jour
    *
    * @return void
    */
   public static function postUpdate(): void
   {
      $input = new InputInterface();
      $output = new OutputInterface();
      $io = new SymfonyStyle($input, $output);

      $io->title('Mise à jour d\'IronFlow Framework');

      // Mise à jour des dépendances
      self::updateDependencies($io);

      $io->success('Mise à jour terminée avec succès !');
   }

   /**
    * Vérifie les prérequis du système
    *
    * @param SymfonyStyle $io
    * @return void
    */
   public static function checkRequirements(SymfonyStyle $io): void
   {
      $io->section('Vérification des prérequis');

      // Vérification de PHP
      if (version_compare(PHP_VERSION, '8.2.0', '<')) {
         throw new \RuntimeException('PHP 8.2 ou supérieur est requis.');
      }

      // Vérification des extensions PHP requises
      $requiredExtensions = ['pdo', 'mbstring', 'xml', 'curl', 'json'];
      foreach ($requiredExtensions as $ext) {
         if (!extension_loaded($ext)) {
            throw new \RuntimeException("L'extension PHP {$ext} est requise.");
         }
      }

      $io->success('Tous les prérequis sont satisfaits.');
   }

   /**
    * Configure l'environnement
    *
    * @param SymfonyStyle $io
    * @return void
    */
   public static function setupEnvironment(SymfonyStyle $io): void
   {
      $io->section('Configuration de l\'environnement');

      if (!file_exists('.env')) {
         copy('.env.example', '.env');
         $io->text('Fichier .env créé à partir de .env.example');
      }

      // Génération de la clé d'application
      $process = new Process(['php', 'artisan', 'key:generate']);
      $process->run();

      if ($process->isSuccessful()) {
         $io->text('Clé d\'application générée');
      }
   }

   /**
    * Installe les dépendances
    *
    * @param SymfonyStyle $io
    * @return void
    */
   public static function installDependencies(SymfonyStyle $io): void
   {
      $io->section('Installation des dépendances');

      // Installation des dépendances Composer
      $process = new Process(['composer', 'install']);
      $process->run();

      if ($process->isSuccessful()) {
         $io->text('Dépendances Composer installées');
      }

      // Installation des dépendances NPM
      if (file_exists('package.json')) {
         $process = new Process(['npm', 'install']);
         $process->run();

         if ($process->isSuccessful()) {
            $io->text('Dépendances NPM installées');
         }
      }
   }

   /**
    * Met à jour les dépendances
    *
    * @param SymfonyStyle $io
    * @return void
    */
   public static function updateDependencies(SymfonyStyle $io): void
   {
      $io->section('Mise à jour des dépendances');

      // Mise à jour des dépendances Composer
      $process = new Process(['composer', 'update']);
      $process->run();

      if ($process->isSuccessful()) {
         $io->text('Dépendances Composer mises à jour');
      }

      // Mise à jour des dépendances NPM
      if (file_exists('package.json')) {
         $process = new Process(['npm', 'update']);
         $process->run();

         if ($process->isSuccessful()) {
            $io->text('Dépendances NPM mises à jour');
         }
      }
   }
}
