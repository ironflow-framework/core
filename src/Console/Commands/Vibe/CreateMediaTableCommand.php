<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Vibe;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Database\Schema\Blueprint;
use IronFlow\Database\Schema\Schema;

class CreateMediaTableCommand extends Command
{
   protected static $defaultName = 'vibe:create-table';
   protected static $defaultDescription = 'Crée la table media pour le système de gestion de médias Vibe';

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);

      $io->title('Création de la table "media" pour Vibe');

      if (Schema::hasTable('media')) {
         $io->warning('La table "media" existe déjà.');

         if (!$io->confirm('Voulez-vous recréer la table? Toutes les données seront perdues.', false)) {
            $io->info('Opération annulée.');
            return Command::SUCCESS;
         }

         Schema::drop('media');
         $io->info('Table "media" supprimée.');
      }

      Schema::create('media', function (Blueprint $table) {
         $table->id();
         $table->string('name');
         $table->string('filename');
         $table->string('path');
         $table->string('mime_type');
         $table->unsignedBigInteger('size');
         $table->string('disk');
         $table->string('extension', 32);
         $table->string('type', 32);
         $table->json('metadata')->nullable();
         $table->string('title')->nullable();
         $table->string('alt')->nullable();
         $table->text('description')->nullable();
         $table->string('model_type')->nullable();
         $table->unsignedBigInteger('model_id')->nullable();
         $table->timestamps();

         $table->index(['model_type', 'model_id']);
         $table->index('type');
      });

      $io->success('Table "media" créée avec succès.');

      // Créer aussi les dossiers de stockage
      $this->createStorageDirectories($io);

      return Command::SUCCESS;
   }

   /**
    * Crée les dossiers de stockage nécessaires
    *
    * @param SymfonyStyle $io
    * @return void
    */
   protected function createStorageDirectories(SymfonyStyle $io): void
   {
      $directories = [
         storage_path('app/public'),
         storage_path('app/public/' . date('Y')),
         storage_path('app/public/' . date('Y/m')),
      ];

      foreach ($directories as $directory) {
         if (!is_dir($directory)) {
            if (mkdir($directory, 0755, true)) {
               $io->info("Dossier créé: {$directory}");
            } else {
               $io->error("Impossible de créer le dossier: {$directory}");
            }
         }
      }
   }
}
