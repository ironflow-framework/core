<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Database\Migrations\MigrationCreator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande de génération de migration
 * 
 * Cette commande permet de créer des fichiers de migration pour la base de données.
 */
class MakeMigrationCommand extends Command
{
   protected static $defaultName = 'make:migration';
   protected static $defaultDescription = 'Crée une nouvelle migration';

   /**
    * Configure la commande
    *
    * @return void
    */
   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom de la migration')
         ->addArgument('table', InputArgument::REQUIRED, 'Le nom de la table')
         ->addArgument('columns', InputArgument::OPTIONAL, 'Les colonnes (format: nom:type,options)');
   }

   /**
    * Exécute la commande
    *
    * @param InputInterface $input
    * @param OutputInterface $output
    * @return int
    */
   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $table = $input->getArgument('table');
      $columns = $input->getArgument('columns') ? explode(',', $input->getArgument('columns')) : [];

      // Utiliser le MigrationCreator pour générer le fichier
      $creator = new MigrationCreator(database_path('migrations'));

      // Détecter si c'est une création de table (par défaut: oui)
      $isCreateTable = true;
      if (strpos($name, 'update_') === 0 || strpos($name, 'alter_') === 0 || strpos($name, 'modify_') === 0) {
         $isCreateTable = false;
      }

      try {
         // Créer le fichier de migration
         $path = $creator->create($name, $table, $isCreateTable);

         // Si la migration est une création de table et que des colonnes sont spécifiées
         // nous allons modifier le fichier pour inclure ces colonnes
         if ($isCreateTable && !empty($columns)) {
            $this->addColumnsToMigration($path, $table, $columns);
         }

         $io->success("La migration {$name} a été créée avec succès : " . basename($path));
         return Command::SUCCESS;
      } catch (\Exception $e) {
         $io->error("Erreur lors de la création de la migration : " . $e->getMessage());
         return Command::FAILURE;
      }
   }

   /**
    * Ajoute des colonnes spécifiées à un fichier de migration existant
    *
    * @param string $path Chemin du fichier de migration
    * @param string $table Nom de la table
    * @param array $columns Colonnes à ajouter
    * @return void
    */
   protected function addColumnsToMigration(string $path, string $table, array $columns): void
   {
      // Lire le contenu du fichier
      $content = file_get_contents($path);

      // Chercher la position après '$table->id();'
      $pattern = '/\$table->id\(\);(\s*?)(\n|\r\n)/';

      // Préparer le contenu des colonnes
      $columnLines = '';
      foreach ($columns as $column) {
         $parts = explode(':', $column);
         $name = $parts[0];
         $type = $parts[1] ?? 'string';
         $options = isset($parts[2]) ? explode('|', $parts[2]) : [];

         $columnLine = "            \$table->{$type}('{$name}'";

         if (!empty($options)) {
            $columnLine .= ", " . implode(', ', array_map(function ($option) {
               return is_numeric($option) ? $option : "'{$option}'";
            }, $options));
         }

         $columnLine .= ");\n";
         $columnLines .= $columnLine;
      }

      // Faire le remplacement
      $content = preg_replace(
         $pattern,
         "\$table->id();\$1\$2$columnLines",
         $content
      );

      // Écrire le fichier mis à jour
      file_put_contents($path, $content);
   }
}
