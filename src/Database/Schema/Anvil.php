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
    */
   protected string $table;

   /**
    * Indique si c'est une modification de table existante
    */
   protected bool $isUpdate;

   /**
    * Liste des colonnes
    */
   protected array $columns = [];

   /**
    * Liste des index
    */
   protected array $indexes = [];

   /**
    * Liste des clés étrangères
    */
   protected array $foreignKeys = [];

   /**
    * Liste des colonnes à supprimer
    */
   protected array $droppedColumns = [];

   /**
    * Liste des index à supprimer
    */
   protected array $droppedIndexes = [];

   /**
    * Liste des contraintes à supprimer
    */
   protected array $droppedConstraints = [];

   /**
    * Colonnes à modifier
    */
   protected array $modifiedColumns = [];

   /**
    * Options de la table
    */
   protected array $tableOptions = [];

   /**
    * Constructeur
    */
   public function __construct(string $table, bool $isUpdate = false)
   {
      $this->table = $table;
      $this->isUpdate = $isUpdate;
   }

   /**
    * Ajoute une colonne de type entier
    */
   public function integer(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('integer', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type big integer
    */
   public function bigInteger(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('bigint', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type small integer
    */
   public function smallInteger(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('smallint', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type tiny integer
    */
   public function tinyInteger(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('tinyint', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type medium integer
    */
   public function mediumInteger(string $name, bool $autoIncrement = false): Column
   {
      return $this->addColumn('mediumint', $name, [
         'auto_increment' => $autoIncrement
      ]);
   }

   /**
    * Ajoute une colonne de type varchar
    */
   public function string(string $name, int $length = 255): Column
   {
      return $this->addColumn('varchar', $name, [
         'length' => $length
      ]);
   }

   /**
    * Ajoute une colonne de type char
    */
   public function char(string $name, int $length = 1): Column
   {
      return $this->addColumn('char', $name, [
         'length' => $length
      ]);
   }

   /**
    * Ajoute une colonne de type texte
    */
   public function text(string $name): Column
   {
      return $this->addColumn('text', $name);
   }

   /**
    * Ajoute une colonne de type medium text
    */
   public function mediumText(string $name): Column
   {
      return $this->addColumn('mediumtext', $name);
   }

   /**
    * Ajoute une colonne de type long text
    */
   public function longText(string $name): Column
   {
      return $this->addColumn('longtext', $name);
   }

   /**
    * Ajoute une colonne de type booléen
    */
   public function boolean(string $name): Column
   {
      return $this->addColumn('boolean', $name);
   }

   /**
    * Ajoute une colonne de type date
    */
   public function date(string $name): Column
   {
      return $this->addColumn('date', $name);
   }

   /**
    * Ajoute une colonne de type datetime
    */
   public function dateTime(string $name): Column
   {
      return $this->addColumn('datetime', $name);
   }

   /**
    * Ajoute une colonne de type time
    */
   public function time(string $name): Column
   {
      return $this->addColumn('time', $name);
   }

   /**
    * Ajoute une colonne de type timestamp
    */
   public function timestamp(string $name): Column
   {
      return $this->addColumn('timestamp', $name);
   }

   /**
    * Ajoute les colonnes created_at et updated_at
    */
   public function timestamps(): void
   {
      $this->dateTime('created_at')->default('CURRENT_TIMESTAMP');
      $this->dateTime('updated_at')->default('CURRENT_TIMESTAMP')->onUpdate("CURRENT_TIMESTAMP");
   }

   /**
    * Ajoute une colonne deleted_at pour le soft delete
    */
   public function softDeletes(): void
   {
      $this->timestamp('deleted_at')->nullable();
   }

   /**
    * Ajoute une colonne de type decimal
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
    */
   public function double(string $name): Column
   {
      return $this->addColumn('double', $name);
   }

   /**
    * Ajoute une colonne de type float
    */
   public function float(string $name): Column
   {
      return $this->addColumn('float', $name);
   }

   /**
    * Ajoute une colonne de type real
    */
   public function real(string $name): Column
   {
      return $this->addColumn('real', $name);
   }

   /**
    * Ajoute une colonne de type enum
    */
   public function enum(string $name, array $values): Column
   {
      return $this->addColumn('enum', $name, [
         'values' => $values
      ]);
   }

   /**
    * Ajoute une colonne de type set
    */
   public function set(string $name, array $values): Column
   {
      return $this->addColumn('set', $name, [
         'values' => $values
      ]);
   }

   /**
    * Ajoute une colonne de type blob
    */
   public function binary(string $name): Column
   {
      return $this->addColumn('blob', $name);
   }

   /**
    * Ajoute une colonne de type json
    */
   public function json(string $name): Column
   {
      return $this->addColumn('json', $name);
   }

   /**
    * Ajoute une colonne de type UUID
    */
   public function uuid(string $name): Column
   {
      return $this->addColumn('uuid', $name);
   }

   /**
    * Ajoute une colonne de type IP
    */
   public function ipAddress(string $name): Column
   {
      return $this->addColumn('ipaddress', $name);
   }

   /**
    * Ajoute une colonne de type MAC address
    */
   public function macAddress(string $name): Column
   {
      return $this->addColumn('macaddress', $name);
   }

   /**
    * Ajoute une clé primaire
    */
   public function primary(string|array $columns): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'primary',
         'columns' => $columns
      ];
   }

   /**
    * Ajoute un index unique
    */
   public function unique(string|array $columns, ?string $name = null): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'unique',
         'columns' => $columns,
         'name' => $name ?? $this->generateIndexName('unique', $columns)
      ];
   }

   /**
    * Ajoute un index
    */
   public function index(string|array $columns, ?string $name = null): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'index',
         'columns' => $columns,
         'name' => $name ?? $this->generateIndexName('index', $columns)
      ];
   }

   /**
    * Ajoute un index spatial
    */
   public function spatialIndex(string|array $columns, ?string $name = null): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'spatial',
         'columns' => $columns,
         'name' => $name ?? $this->generateIndexName('spatial', $columns)
      ];
   }

   /**
    * Ajoute un index fulltext
    */
   public function fullText(string|array $columns, ?string $name = null): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->indexes[] = [
         'type' => 'fulltext',
         'columns' => $columns,
         'name' => $name ?? $this->generateIndexName('fulltext', $columns)
      ];
   }

   /**
    * Ajoute une clé étrangère
    */
   public function foreign(string|array $columns, string $refTable, string|array $refColumns = ['id'], ?string $name = null): ForeignKey
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $refColumns = is_array($refColumns) ? $refColumns : [$refColumns];

      $foreignKey = new ForeignKey(
         $columns,
         $refTable,
         $refColumns,
         $name ?? $this->generateForeignKeyName($columns)
      );
      $this->foreignKeys[] = $foreignKey;

      return $foreignKey;
   }

   /**
    * Supprime une colonne
    */
   public function dropColumn(string|array $columns): void
   {
      $columns = is_array($columns) ? $columns : [$columns];
      $this->droppedColumns = array_merge($this->droppedColumns, $columns);
   }

   /**
    * Supprime un index
    */
   public function dropIndex(string|array $index): void
   {
      $this->droppedIndexes[] = is_array($index) ? $this->generateIndexName('index', $index) : $index;
   }

   /**
    * Supprime un index unique
    */
   public function dropUnique(string|array $index): void
   {
      $this->droppedIndexes[] = is_array($index) ? $this->generateIndexName('unique', $index) : $index;
   }

   /**
    * Supprime une clé primaire
    */
   public function dropPrimary(): void
   {
      $this->droppedIndexes[] = 'PRIMARY';
   }

   /**
    * Supprime une clé étrangère
    */
   public function dropForeign(string|array $columns): void
   {
      $name = is_array($columns) ? $this->generateForeignKeyName($columns) : $columns;
      $this->droppedConstraints[] = $name;
   }

   /**
    * Modifie une colonne existante
    */
   public function modifyColumn(string $name): Column
   {
      if (!isset($this->columns[$name])) {
         throw new \InvalidArgumentException("Column '{$name}' does not exist");
      }

      $column = clone $this->columns[$name];
      $this->modifiedColumns[$name] = $column;
      return $column;
   }

   /**
    * Renomme une colonne
    */
   public function renameColumn(string $from, string $to): void
   {
      if (!isset($this->columns[$from])) {
         throw new \InvalidArgumentException("Column '{$from}' does not exist");
      }

      $column = $this->columns[$from];
      unset($this->columns[$from]);
      $this->columns[$to] = $column;
      $column->rename($to);
   }

   /**
    * Ajoute une colonne ID auto-incrémentée
    */
   public function id(): Column
   {
      $column = $this->bigInteger('id', true);
      $this->primary('id');
      return $column;
   }

   /**
    * Ajoute une colonne remember_token
    */
   public function rememberToken(): Column
   {
      return $this->string('remember_token', 100)->nullable();
   }

   /**
    * Ajoute une colonne pour un ID étranger
    */
   public function foreignId(string $name): Column
   {
      return $this->bigInteger($name . '_id');
   }

   /**
    * Ajoute une colonne pour un ID étranger avec contrainte
    */
   public function foreignIdFor(string $model, ?string $column = null): Column
   {
      $columnName = $column ?? strtolower($model) . '_id';
      $tableName = strtolower($model) . 's'; // Convention plurielle simple

      $foreignColumn = $this->foreignId(rtrim($columnName, '_id'));
      $this->foreign($columnName, $tableName, ['id']);

      return $foreignColumn;
   }

   /**
    * Définit les options de la table
    */
   public function engine(string $engine): self
   {
      $this->tableOptions['engine'] = $engine;
      return $this;
   }

   /**
    * Définit le charset de la table
    */
   public function charset(string $charset): self
   {
      $this->tableOptions['charset'] = $charset;
      return $this;
   }

   /**
    * Définit la collation de la table
    */
   public function collation(string $collation): self
   {
      $this->tableOptions['collation'] = $collation;
      return $this;
   }

   /**
    * Ajoute un commentaire à la table
    */
   public function comment(string $comment): self
   {
      $this->tableOptions['comment'] = $comment;
      return $this;
   }

   /**
    * Ajoute une colonne à la table
    */
   protected function addColumn(string $type, string $name, array $parameters = []): Column
   {
      $column = new Column($type, $name, $parameters);
      $this->columns[$name] = $column;
      return $column;
   }

   /**
    * Génère un nom d'index
    */
   protected function generateIndexName(string $type, array $columns): string
   {
      $name = $this->table . '_' . implode('_', $columns) . '_' . $type;
      return substr($name, 0, 64); // Limite MySQL
   }

   /**
    * Génère un nom de clé étrangère
    */
   protected function generateForeignKeyName(array $columns): string
   {
      $name = $this->table . '_' . implode('_', $columns) . '_foreign';
      return substr($name, 0, 64); // Limite MySQL
   }

   /**
    * Vérifie si une colonne existe
    */
   public function hasColumn(string $name): bool
   {
      return isset($this->columns[$name]);
   }

   /**
    * Récupère une colonne
    */
   public function getColumn(string $name): ?Column
   {
      return $this->columns[$name] ?? null;
   }

   /**
    * Récupère toutes les colonnes
    */
   public function getColumns(): array
   {
      return $this->columns;
   }

   /**
    * Convertit le plan en requêtes SQL
    */
   public function toSql(string $driver): array
   {
      $statements = [];

      if (!$this->isUpdate) {
         $statements = array_merge($statements, $this->generateCreateTableSql($driver));
      } else {
         $statements = array_merge($statements, $this->generateAlterTableSql($driver));
      }

      return $statements;
   }

   /**
    * Génère le SQL de création de table
    */
   protected function generateCreateTableSql(string $driver): array
   {
      $statements = [];

      $sql = "CREATE TABLE IF NOT EXISTS {$this->table} (";

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

      // Options de table (MySQL)
      if ($driver === 'mysql' && !empty($this->tableOptions)) {
         $options = [];
         if (isset($this->tableOptions['engine'])) {
            $options[] = "ENGINE={$this->tableOptions['engine']}";
         }
         if (isset($this->tableOptions['charset'])) {
            $options[] = "DEFAULT CHARSET={$this->tableOptions['charset']}";
         }
         if (isset($this->tableOptions['collation'])) {
            $options[] = "COLLATE={$this->tableOptions['collation']}";
         }
         if (isset($this->tableOptions['comment'])) {
            $options[] = "COMMENT='{$this->tableOptions['comment']}'";
         }

         if (!empty($options)) {
            $sql .= " " . implode(" ", $options);
         }
      }

      $statements[] = $sql;

      // Ajouter les index non-primaires
      foreach ($this->indexes as $index) {
         if ($index['type'] !== 'primary') {
            $statements[] = $this->generateIndexSql($index, $driver);
         }
      }

      // Ajouter les clés étrangères
      foreach ($this->foreignKeys as $foreignKey) {
         $statements[] = $foreignKey->toSql($this->table, $driver);
      }

      return $statements;
   }

   /**
    * Génère le SQL de modification de table
    */
   protected function generateAlterTableSql(string $driver): array
   {
      $statements = [];

      // Supprimer les contraintes
      foreach ($this->droppedConstraints as $constraint) {
         $statements[] = "ALTER TABLE {$this->table} DROP FOREIGN KEY {$constraint}";
      }

      // Supprimer les index
      foreach ($this->droppedIndexes as $index) {
         if ($index === 'PRIMARY') {
            $statements[] = "ALTER TABLE {$this->table} DROP PRIMARY KEY";
         } else {
            $statements[] = "ALTER TABLE {$this->table} DROP INDEX {$index}";
         }
      }

      // Supprimer les colonnes
      foreach ($this->droppedColumns as $column) {
         $statements[] = "ALTER TABLE {$this->table} DROP COLUMN {$column}";
      }

      // Modifier les colonnes
      foreach ($this->modifiedColumns as $name => $column) {
         $statements[] = "ALTER TABLE {$this->table} MODIFY COLUMN " . $column->toSql($driver);
      }

      // Ajouter les nouvelles colonnes
      foreach ($this->columns as $column) {
         $statements[] = "ALTER TABLE {$this->table} ADD COLUMN " . $column->toSql($driver);
      }

      // Ajouter les index
      foreach ($this->indexes as $index) {
         if ($index['type'] === 'primary') {
            $statements[] = "ALTER TABLE {$this->table} ADD PRIMARY KEY (" . implode(', ', $index['columns']) . ")";
         } else {
            $statements[] = $this->generateIndexSql($index, $driver);
         }
      }

      // Ajouter les clés étrangères
      foreach ($this->foreignKeys as $foreignKey) {
         $statements[] = $foreignKey->toSql($this->table, $driver);
      }

      return $statements;
   }

   /**
    * Génère le SQL pour un index
    */
   protected function generateIndexSql(array $index, string $driver): string
   {
      $indexType = match ($index['type']) {
         'unique' => 'UNIQUE ',
         'fulltext' => 'FULLTEXT ',
         'spatial' => 'SPATIAL ',
         default => ''
      };

      return "CREATE {$indexType}INDEX {$index['name']} ON {$this->table} (" . implode(', ', $index['columns']) . ")";
   }
}
