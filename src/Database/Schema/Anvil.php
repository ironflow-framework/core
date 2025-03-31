<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

/**
 * Plan pour la création ou modification de table de base de données
 */
class Anvil
{
   /**
    * Nom de la table
    *
    * @var string
    */
   protected string $table;

   /**
    * Indique si c'est une modification de table existante
    *
    * @var bool
    */
   protected bool $isUpdate;

   /**
    * Liste des colonnes
    *
    * @var array
    */
   protected array $columns = [];

   /**
    * Liste des index
    *
    * @var array
    */
   protected array $indexes = [];

   /**
    * Liste des clés étrangères
    *
    * @var array
    */
   protected array $foreignKeys = [];

   /**
    * Liste des colonnes à supprimer
    *
    * @var array
    */
   protected array $droppedColumns = [];

   /**
    * Constructeur
    *
    * @param string $table Nom de la table
    * @param bool $isUpdate Est-ce une mise à jour d'une table existante
    */
   public function __construct(string $table, bool $isUpdate = false)
   {
      $this->table = $table;
      $this->isUpdate = $isUpdate;
   }

   /**
    * Ajoute une colonne de type entier
    *
    * @param string $name Nom de la colonne
    * @param bool $autoIncrement Auto-incrémentation
    * @return Column
    */
   public function integer(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('integer', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type big integer
    *
    * @param string $name Nom de la colonne
    * @param bool $autoIncrement Auto-incrémentation
    * @return Column
    */
   public function bigInteger(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('bigint', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type varchar
    *
    * @param string $name Nom de la colonne
    * @param int $length Longueur maximale
    * @return Column
    */
   public function string(string $name, int $length = 255): Column
   {
      return $this->addColumn('varchar', $name, [
         'length' => $length
      ]);
   }

   /**
    * Ajoute une colonne de type texte
    *
    * @param string $name Nom de la colonne
    * @return Column
    */
   public function text(string $name): Column
   {
      return $this->addColumn('text', $name);
   }

   /**
    * Ajoute une colonne de type booléen
    *
    * @param string $name Nom de la colonne
    * @return Column
    */
   public function boolean(string $name): Column
   {
      return $this->addColumn('boolean', $name);
   }

   /**
    * Ajoute une colonne de type date
    *
    * @param string $name Nom de la colonne
    * @return Column
    */
   public function date(string $name): Column
   {
      return $this->addColumn('date', $name);
   }

   /**
    * Ajoute une colonne de type datetime
    *
    * @param string $name Nom de la colonne
    * @return Column
    */
   public function dateTime(string $name): Column
   {
      return $this->addColumn('datetime', $name);
   }

   /**
    * Ajoute une colonne de type timestamp
    *
    * @param string $name Nom de la colonne
    * @return Column
    */
   public function timestamp(string $name): Column
   {
      return $this->addColumn('timestamp', $name);
   }

   /**
    * Ajoute les colonnes created_at et updated_at
    *
    * @return void
    */
   public function timestamps(): void
   {
      $this->timestamp('created_at')->nullable();
      $this->timestamp('updated_at')->nullable();
   }

   /**
    * Ajoute une colonne deleted_at
    * @return void
    */
   public function softDeletes(): void
   {
      $this->timestamp('deleted_at')->nullable();
   }

   /**
    * Ajoute une colonne de type decimal
    *
    * @param string $name Nom de la colonne
    * @param int $precision Précision
    * @param int $scale Échelle
    * @return Column
    */
   public function decimal(string $name, int $precision = 8, int $scale = 2): Column
   {
      return $this->addColumn('decimal', $name, [
         'precision' => $precision,
         'scale' => $scale
      ]);
   }

   /**
    * Ajoute une colonne de type double
    * @param string $name
    * @return Column
    */
   public function double(string $name): Column
   {
      return $this->addColumn('double', $name);
   }

   /**
    * Ajoute une colonne de type float
    *
    * @param string $name Nom de la colonne
    * @return Column
    */
   public function float(string $name): Column
   {
      return $this->addColumn('float', $name);
   }

   /**
    * Ajoute une colonne de type real
    * @param string $name
    * @return Column
    */
   public function real(string $name): Column
   {
      return $this->addColumn('real', $name);
   }

   /**
    * Ajoute une colonne de type enum
    * @param string $name
    * @param array $values possibles
    * @return Column
    */
   public function enum(string $name, array $values): Column
   {
      return $this->addColumn("enum('" . implode("','", $values) . "')", $name);
   }


   /**
    * Ajoute une colonne de type blob
    * @param string $name
    * @return Column
    */
   public function binary(string $name): Column
   {
      return $this->addColumn('blob', $name);
   }

   /**
    * Ajoute une colonne de type json
    * @param string $name
    * @return Column
    */
   public function json(string $name): Column
   {
      return $this->addColumn('json', $name);
   }

   /**
    * Ajoute une clé primaire
    *
    * @param string|array $columns Colonne(s) pour la clé primaire
    * @return void
    */
   public function primary($columns): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'primary',
         'columns' => $columns
      ];
   }

   /**
    * Ajoute un index unique
    *
    * @param string|array $columns Colonne(s) pour l'index
    * @param string|null $name Nom de l'index
    * @return void
    */
   public function unique($columns, ?string $name = null): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'unique',
         'columns' => $columns,
         'name' => $name ?? $this->table . '_' . implode('_', $columns) . '_unique'
      ];
   }

   /**
    * Ajoute un index
    *
    * @param string|array $columns Colonne(s) pour l'index
    * @param string|null $name Nom de l'index
    * @return void
    */
   public function index($columns, ?string $name = null): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'index',
         'columns' => $columns,
         'name' => $name ?? $this->table . '_' . implode('_', $columns) . '_index'
      ];
   }

   /**
    * Ajoute une clé étrangère
    *
    * @param string|array $columns Colonne(s) pour la clé étrangère
    * @param string $refTable Table référencée
    * @param string|array $refColumns Colonne(s) référencée(s)
    * @param string|null $name Nom de la contrainte
    * @return ForeignKey
    */
   public function foreign($columns, string $refTable, $refColumns = ['id'], ?string $name = null): ForeignKey
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $refColumns = is_array($refColumns) ? $refColumns : [$refColumns];

      $foreignKey = new ForeignKey($columns, $refTable, $refColumns, $name ?? $this->table . '_' . implode('_', $columns) . '_foreign');
      $this->foreignKeys[] = $foreignKey;

      return $foreignKey;
   }

   /**
    * Supprime une colonne
    *
    * @param string|array $columns Colonne(s) à supprimer
    * @return void
    */
   public function dropColumn($columns): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->droppedColumns = array_merge($this->droppedColumns, $columns);
   }

   /**
    * Ajoute une colonne ID auto-incrémentée
    *
    * @return Column
    */
   public function id(): Column
   {
      $column = $this->bigInteger('id', true);
      $this->primary('id');
      return $column;
   }

   /**
    * Ajoute une colonne remember_token de type varchar
    * @return Column
    */
   public function rememberToken(): Column
   {
      return $this->string('remember_token', 100)->nullable();
   }


   /**
    * Ajoute une colonne pour stocker l'identifiant d'un modèle lié
    *
    * @param string $name Nom du modèle lié
    * @return Column
    */
   public function foreignId(string $name): Column
   {
      return $this->bigInteger($name . '_id');
   }

   /**
    * Ajoute une colonne à la table
    *
    * @param string $type Type de la colonne
    * @param string $name Nom de la colonne
    * @param array $parameters Paramètres additionnels
    * @return Column
    */
   protected function addColumn(string $type, string $name, array $parameters = []): Column
   {
      $column = new Column($type, $name, $parameters);
      $this->columns[$name] = $column;
      return $column;
   }

   /**
    * Convertit le plan en requêtes SQL
    *
    * @param string $driver Type de base de données
    * @return array
    */
   public function toSql(string $driver): array
   {
      $statements = [];

      if (!$this->isUpdate) {
         // Création de table
         $sql = "CREATE TABLE {$this->table} (";

         // Colonnes
         $columnDefinitions = [];
         foreach ($this->columns as $column) {
            $columnDefinitions[] = $column->toSql($driver);
         }

         // Clés primaires
         foreach ($this->indexes as $index) {
            if ($index['type'] === 'primary') {
               $columnDefinitions[] = "PRIMARY KEY (" . implode(', ', $index['columns']) . ")";
            }
         }

         $sql .= implode(", ", $columnDefinitions);
         $sql .= ")";

         $statements[] = $sql;

         // Ajouter les index qui ne sont pas des clés primaires
         foreach ($this->indexes as $index) {
            if ($index['type'] !== 'primary') {
               $indexType = $index['type'] === 'unique' ? 'UNIQUE ' : '';
               $statements[] = "CREATE {$indexType}INDEX {$index['name']} ON {$this->table} (" . implode(', ', $index['columns']) . ")";
            }
         }

         // Ajouter les clés étrangères
         foreach ($this->foreignKeys as $foreignKey) {
            $statements[] = $foreignKey->toSql($this->table, $driver);
         }
      } else {
         // Modification de table

         // Ajouter des colonnes
         foreach ($this->columns as $column) {
            $statements[] = "ALTER TABLE {$this->table} ADD COLUMN " . $column->toSql($driver);
         }

         // Supprimer des colonnes
         foreach ($this->droppedColumns as $column) {
            $statements[] = "ALTER TABLE {$this->table} DROP COLUMN {$column}";
         }

         // Ajouter des index
         foreach ($this->indexes as $index) {
            if ($index['type'] === 'primary') {
               // La modification d'une clé primaire est complexe et varie selon la BD
               // Cette logique devrait être étendue pour chaque type de BD
               $statements[] = "ALTER TABLE {$this->table} ADD PRIMARY KEY (" . implode(', ', $index['columns']) . ")";
            } else {
               $indexType = $index['type'] === 'unique' ? 'UNIQUE ' : '';
               $statements[] = "CREATE {$indexType}INDEX {$index['name']} ON {$this->table} (" . implode(', ', $index['columns']) . ")";
            }
         }

         // Ajouter des clés étrangères
         foreach ($this->foreignKeys as $foreignKey) {
            $statements[] = $foreignKey->toSql($this->table, $driver);
         }
      }

      return $statements;
   }
}
