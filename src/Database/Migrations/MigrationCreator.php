<?php

declare(strict_types=1);

namespace IronFlow\Database\Migrations;

/**
 * Classe pour la création de fichiers de migration
 */
class MigrationCreator
{
   /**
    * Le chemin vers le répertoire de migrations
    *
    * @var string
    */
   protected string $path;

   /**
    * Constructeur
    *
    * @param string $path Chemin vers le répertoire des migrations
    */
   public function __construct(string $path)
   {
      $this->path = rtrim($path, '/\\');
   }

   /**
    * Crée un nouveau fichier de migration
    *
    * @param string $name Nom de la migration
    * @param string $table Nom de la table concernée
    * @param bool $create Indique s'il s'agit d'une création de table
    * @return string Chemin du fichier créé
    */
   public function create(string $name, string $table, bool $create = false): string
   {
      $filename = $this->getFilename($name);
      $path = $this->path . '/' . $filename;

      // Création du répertoire si nécessaire
      if (!is_dir($this->path)) {
         mkdir($this->path, 0755, true);
      }

      // Génération du contenu du fichier
      if ($create) {
         $stub = $this->getCreateTableStub($table);
      } else {
         $stub = $this->getUpdateTableStub($table);
      }

      // Écriture du fichier
      file_put_contents($path, $stub);

      return $path;
   }

   /**
    * Génère un nom de fichier pour la migration
    *
    * @param string $name Nom de la migration
    * @return string
    */
   protected function getFilename(string $name): string
   {
      // Format: YYYY_MM_DD_HHMMSS_name.php
      $date = date('Y_m_d_His');
      $name = strtolower(str_replace(' ', '_', $name));

      return $date . '_' . $name . '.php';
   }

   /**
    * Obtient le modèle pour la création de table
    *
    * @param string $table Nom de la table
    * @return string
    */
   protected function getCreateTableStub(string $table): string
   {
      $className = $this->getClassName($table, true);

      return <<<EOT
<?php

declare(strict_types=1);

namespace Database\Migrations;

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        \$this->schema->create('{$table}', function (Blueprint \$table) {
            \$table->id();
            // Ajoutez vos colonnes ici
            
            \$table->timestamps();
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        \$this->schema->dropIfExists('{$table}');
    }
};
EOT;
   }

   /**
    * Obtient le modèle pour la mise à jour de table
    *
    * @param string $table Nom de la table
    * @return string
    */
   protected function getUpdateTableStub(string $table): string
   {
      $className = $this->getClassName($table, false);

      return <<<EOT
<?php

declare(strict_types=1);

use IronFlow\Database\Migrations\Migration;
use IronFlow\Database\Schema\Blueprint;

class {$className} extends Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        \$this->schema->table('{$table}', function (Blueprint \$table) {
            // Ajoutez vos colonnes ou modifications ici
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        \$this->schema->table('{$table}', function (Blueprint \$table) {
            // Annulez vos modifications ici
        });
    }
}
EOT;
   }

   /**
    * Génère un nom de classe pour la migration
    *
    * @param string $table Nom de la table
    * @param bool $create Indique s'il s'agit d'une création de table
    * @return string
    */
   protected function getClassName(string $table, bool $create): string
   {
      $prefix = $create ? 'Create' : 'Update';

      // Formater le nom de la table en CamelCase
      $table = str_replace('_', ' ', $table);
      $table = ucwords($table);
      $table = str_replace(' ', '', $table);

      // Ajouter "Table" à la fin
      return $prefix . $table . 'Table';
   }
}
