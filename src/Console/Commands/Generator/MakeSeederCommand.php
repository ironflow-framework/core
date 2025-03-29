<?php

namespace IronFlow\Console\Commands\Generator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour générer un seeder
 * 
 * Cette commande permet de créer de nouveaux seeders pour la base de données.
 */
class MakeSeederCommand extends Command
{
    protected static $defaultName = 'make:seeder';
    protected static $defaultDescription = 'Crée un nouveau seeder';

    /**
     * Configure la commande
     * 
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::REQUIRED, 'Le nom du seeder (sans le suffixe "Seeder")')
            ->addArgument('model', InputArgument::OPTIONAL, 'Le modèle associé (optionnel)');
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
        $model = $input->getArgument('model');

        // Ajouter le suffixe Seeder si non présent
        if (!str_ends_with($name, 'Seeder')) {
            $name .= 'Seeder';
        }

        $seederContent = $this->generateSeederContent($name, $model);
        $seederPath = database_path("seeders/{$name}.php");

        // Création du répertoire si nécessaire
        $directory = dirname($seederPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Écriture du fichier
        file_put_contents($seederPath, $seederContent);
        $io->success("Le seeder {$name} a été créé avec succès !");

        // Mise à jour du DatabaseSeeder pour inclure ce seeder
        $this->updateDatabaseSeeder($name, $io);

        return Command::SUCCESS;
    }

    /**
     * Génère le contenu du seeder
     * 
     * @param string $name Nom du seeder
     * @param string|null $model Modèle associé
     * @return string
     */
    protected function generateSeederContent(string $name, ?string $model): string
    {
        // Extraire le nom de classe (sans le suffixe Seeder)
        $baseName = str_ends_with($name, 'Seeder') ? substr($name, 0, -6) : $name;

        // Créer les import pour le modèle si nécessaire
        $modelUse = '';
        $modelInsert = '';

        if ($model) {
            $modelClass = "App\\Models\\{$model}";
            $modelUse = "use {$modelClass};\n";

            // Ajouter le code d'insertion pour le modèle (à ajuster selon le cas)
            $modelInsert = <<<PHP
      // Insertion de données via le modèle
      {$model}::create([
         // Ajoutez vos données ici
         'name' => 'Exemple',
         'created_at' => date('Y-m-d H:i:s'),
         'updated_at' => date('Y-m-d H:i:s'),
      ]);

      // Vous pouvez également utiliser la méthode insertMany directement
      /*
      \$this->insertMany('{$model}s', [
         [
            'name' => 'Exemple 1',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
         ],
         [
            'name' => 'Exemple 2',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
         ],
      ]);
      */
PHP;
        } else {
            // Exemple de base pour l'insertion sans modèle
            $modelInsert = <<<PHP
      // Insertion de données directement via PDO
      \$this->insert('exemple_table', [
         'name' => 'Exemple',
         'created_at' => date('Y-m-d H:i:s'),
         'updated_at' => date('Y-m-d H:i:s'),
      ]);
PHP;
        }

        return <<<PHP
<?php

declare(strict_types=1);

namespace Database\Seeder;

use IronFlow\Database\Seeder\Seeder;
{$modelUse}

/**
 * Seeder pour populer la table avec des données
 */
class {$name} extends Seeder
{
   /**
    * Exécute les opérations de seeding
    *
    * @return void
    */
   public function run(): void
   {
{$modelInsert}
   }
}
PHP;
    }

    /**
     * Met à jour le DatabaseSeeder pour inclure le nouveau seeder
     *
     * @param string $seederName Nom du seeder
     * @param SymfonyStyle $io Interface de sortie
     * @return void
     */
    protected function updateDatabaseSeeder(string $seederName, SymfonyStyle $io): void
    {
        $databaseSeederPath = database_path('seeders/DatabaseSeeder.php');

        // Vérifier si le fichier existe
        if (!file_exists($databaseSeederPath)) {
            $io->warning("DatabaseSeeder.php n'existe pas, impossible de mettre à jour.");
            return;
        }

        // Lire le contenu du fichier
        $content = file_get_contents($databaseSeederPath);

        // Vérifier si le seeder est déjà inclus
        if (strpos($content, $seederName . '::class') !== false) {
            $io->info("Le seeder {$seederName} est déjà inclus dans DatabaseSeeder.");
            return;
        }

        // Chercher la position pour ajouter le nouveau seeder
        $pattern = '/public\s+function\s+run\(\)(?:[^}]*?)(?:\s*?\/\/\s*?Les seeders seront ajoutés ici|\s*?\{)/s';

        if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
            $position = $matches[0][1] + strlen($matches[0][0]);

            // Préparer la ligne à ajouter
            $line = "\n      \$this->call({$seederName}::class);";

            // Insérer la ligne
            $content = substr_replace($content, $line, $position, 0);

            // Sauvegarder le fichier
            file_put_contents($databaseSeederPath, $content);
            $io->success("DatabaseSeeder.php a été mis à jour pour inclure {$seederName}.");
        } else {
            $io->warning("Impossible de trouver l'endroit où ajouter le seeder dans DatabaseSeeder.php");
        }
    }
}
