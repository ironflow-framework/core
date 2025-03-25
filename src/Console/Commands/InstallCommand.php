<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallCommand extends Command
{
   protected static $defaultName = 'install';
   protected static $defaultDescription = 'Installe le framework IronFlow';

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $io->title('Installation de IronFlow');

      // Créer les répertoires nécessaires
      $this->createDirectories($io);

      // Copier les fichiers de configuration
      $this->copyConfigFiles($io);

      // Créer le fichier .env
      $this->createEnvFile($io);

      // Créer le fichier composer.json
      $this->createComposerJson($io);

      // Créer le fichier README.md
      $this->createReadme($io);

      // Créer le fichier .gitignore
      $this->createGitignore($io);

      $io->success('IronFlow a été installé avec succès !');
      $io->text('Pour commencer à utiliser le framework, exécutez :');
      $io->text('1. composer install');
      $io->text('2. php forge serve');

      return Command::SUCCESS;
   }

   protected function createDirectories(SymfonyStyle $io): void
   {
      $directories = [
         'src/Http/Controllers',
         'src/Http/Middleware',
         'src/Models',
         'src/View/Components',
         'src/View/Components/Forms',
         'src/View/Components/Layout',
         'src/View/Components/UI',
         'src/Services',
         'src/Validation',
         'src/Database/Migrations',
         'src/Database/Seeders',
         'src/Database/Factories',
         'resources/views',
         'resources/views/layouts',
         'resources/views/components',
         'public/css',
         'public/js',
         'public/images',
         'tests/Unit',
         'tests/Feature',
         'config',
         'routes',
         'storage/logs',
         'storage/cache',
         'storage/sessions'
      ];

      foreach ($directories as $directory) {
         if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
            $io->text("Création du répertoire : {$directory}");
         }
      }
   }

   protected function copyConfigFiles(SymfonyStyle $io): void
   {
      $configFiles = [
         'app.php' => [
            'name' => 'IronFlow',
            'env' => 'local',
            'debug' => true,
            'url' => 'http://localhost',
            'timezone' => 'Europe/Paris',
            'locale' => 'fr',
            'key' => 'base64:' . base64_encode(random_bytes(32)),
            'cipher' => 'AES-256-CBC'
         ],
         'database.php' => [
            'default' => 'mysql',
            'connections' => [
               'mysql' => [
                  'driver' => 'mysql',
                  'host' => 'localhost',
                  'database' => 'ironflow',
                  'username' => 'root',
                  'password' => '',
                  'charset' => 'utf8mb4',
                  'collation' => 'utf8mb4_unicode_ci',
                  'prefix' => ''
               ]
            ]
         ],
         'mail.php' => [
            'default' => 'smtp',
            'mailers' => [
               'smtp' => [
                  'transport' => 'smtp',
                  'host' => 'smtp.mailtrap.io',
                  'port' => 2525,
                  'username' => null,
                  'password' => null,
                  'encryption' => null
               ]
            ],
            'from' => [
               'address' => 'hello@example.com',
               'name' => 'IronFlow'
            ]
         ]
      ];

      foreach ($configFiles as $file => $content) {
         $path = "config/{$file}";
         if (!file_exists($path)) {
            file_put_contents($path, "<?php\n\nreturn " . var_export($content, true) . ";\n");
            $io->text("Création du fichier de configuration : {$file}");
         }
      }
   }

   protected function createEnvFile(SymfonyStyle $io): void
   {
      $envContent = <<<ENV
APP_NAME=IronFlow
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ironflow
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="IronFlow"
ENV;

      if (!file_exists('.env')) {
         file_put_contents('.env', $envContent);
         $io->text('Création du fichier .env');
      }
   }

   protected function createComposerJson(SymfonyStyle $io): void
   {
      $composerContent = <<<JSON
{
    "name": "ironflow/framework",
    "description": "Un framework PHP moderne et élégant",
    "type": "project",
    "require": {
        "php": "^8.1",
        "symfony/console": "^6.0",
        "symfony/http-foundation": "^6.0",
        "symfony/routing": "^6.0",
        "symfony/var-dumper": "^6.0",
        "vlucas/phpdotenv": "^5.5",
        "monolog/monolog": "^3.0",
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "IronFlow\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit"
    },
    "config": {
        "optimize-autoloader": true,
        "preferred-install": "dist",
        "sort-packages": true
    },
    "minimum-stability": "stable",
    "prefer-stable": true
}
JSON;

      if (!file_exists('composer.json')) {
         file_put_contents('composer.json', $composerContent);
         $io->text('Création du fichier composer.json');
      }
   }

   protected function createReadme(SymfonyStyle $io): void
   {
      $readmeContent = <<<MD
# IronFlow Framework

Un framework PHP moderne et élégant inspiré par Laravel.

## Prérequis

- PHP 8.1 ou supérieur
- Composer
- MySQL 5.7 ou supérieur

## Installation

1. Clonez le dépôt :
\`\`\`bash
git clone https://github.com/votre-username/ironflow.git
\`\`\`

2. Installez les dépendances :
\`\`\`bash
composer install
\`\`\`

3. Copiez le fichier .env.example vers .env :
\`\`\`bash
cp .env.example .env
\`\`\`

4. Générez la clé d'application :
\`\`\`bash
php forge key:generate
\`\`\`

5. Configurez votre base de données dans le fichier .env

6. Exécutez les migrations :
\`\`\`bash
php forge migrate
\`\`\`

7. Lancez le serveur de développement :
\`\`\`bash
php forge serve
\`\`\`

## Documentation

La documentation complète est disponible dans le dossier `docs/`.

## Tests

Pour exécuter les tests :
\`\`\`bash
composer test
\`\`\`

## Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.
MD;

      if (!file_exists('README.md')) {
         file_put_contents('README.md', $readmeContent);
         $io->text('Création du fichier README.md');
      }
   }

   protected function createGitignore(SymfonyStyle $io): void
   {
      $gitignoreContent = <<<GITIGNORE
/vendor/
/node_modules/
/public/hot
/public/storage
/storage/*.key
/storage/logs/*
/storage/cache/*
/storage/sessions/*
.env
.env.backup
.phpunit.result.cache
docker-compose.override.yml
Homestead.json
Homestead.yaml
npm-debug.log
yarn-error.log
/.fleet
/.idea
/.vscode
GITIGNORE;

      if (!file_exists('.gitignore')) {
         file_put_contents('.gitignore', $gitignoreContent);
         $io->text('Création du fichier .gitignore');
      }
   }
}
