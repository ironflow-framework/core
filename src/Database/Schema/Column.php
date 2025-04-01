<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

/**
 * Classe représentant une colonne dans un schéma de base de données
 */
class Column
{
   /**
    * Type de la colonne
    *
    * @var string
    */
   protected string $type;

   /**
    * Nom de la colonne
    *
    * @var string
    */
   protected string $name;

   /**
    * Paramètres de la colonne
    *
    * @var array
    */
   protected array $parameters;

   /**
    * Indique si la colonne est nullable
    *
    * @var bool
    */
   protected bool $nullable = false;

   /**
    * Valeur par défaut de la colonne
    *
    * @var mixed
    */
   protected $default = null;

   /**
    * Indique si la colonne a une valeur par défaut
    *
    * @var bool
    */
   protected bool $hasDefault = false;

   /**
    * Indique si la colonne est unique
    *
    * @var bool
    */
   protected bool $unique = false;

   /**
    * Commentaire sur la colonne
    *
    * @var string|null
    */
   protected ?string $comment = null;

   /**
    * Table référencée par la colonne
    *
    * @var array|null
    */
   protected ?array $constrained = null;

   /**
    * Action à effectuer lors de la suppression de la colonne
    *
    * @var string|null
    */
   protected ?string $onDelete = null;
   
   /**
    * Action à effectuer lors de la mise à jour de la colonne
    *
    * @var string|null
    */
   protected ?string $onUpdate = null;

   /**
    * Constructeur
    *
    * @param string $type Type de la colonne
    * @param string $name Nom de la colonne
    * @param array $parameters Paramètres additionnels
    */
   public function __construct(string $type, string $name, array $parameters = [])
   {
      $this->type = $type;
      $this->name = $name;
      $this->parameters = $parameters;
   }

   /**
    * Définit la colonne comme nullable
    *
    * @return $this
    */
   public function nullable(): self
   {
      $this->nullable = true;
      return $this;
   }

   /**
    * Définit la valeur par défaut de la colonne
    *
    * @param mixed $value Valeur par défaut
    * @return $this
    */
   public function default($value): self
   {
      $this->default = $value;
      $this->hasDefault = true;
      return $this;
   }

   /**
    * Définit la colonne comme unique
    *
    * @return $this
    */
   public function unique(): self
   {
      $this->unique = true;
      return $this;
   }

   /**
    * Ajoute un commentaire à la colonne
    *
    * @param string $comment Commentaire
    * @return $this
    */
   public function comment(string $comment): self
   {
      $this->comment = $comment;
      return $this;
   }

   /**
    * Définit la table référencée par la colonne
    *
    * @param array|null $reference [table, column]
    * @return $this
    */
   public function constrained(?array $reference = null): self
   {
      $this->constrained = $reference;
      return $this;
   }

   /**
    * Définit l'action à effectuer lors de la suppression de la colonne
    *
    * @param string $action Action à effectuer
    * @return $this
    */
   public function onDelete(string $action): self
   {
      $this->onDelete = $action;
      return $this;
   }

   /**
    * Définit l'action à effectuer lors de la mise à jour de la colonne
    *
    * @param string $action Action à effectuer
    * @return $this
    */
   public function onUpdate(string $action): self
   {
      $this->onUpdate = $action;
      return $this;
   }

   /**
    * Convertit la colonne en définition SQL
    *
    * @param string $driver Type de base de données
    * @return string
    */
   public function toSql(string $driver): string
   {
      $sql = $this->name . ' ' . $this->getSqlType($driver);

      // Ajouter NOT NULL ou NULL
      if (!$this->nullable) {
         $sql .= ' NOT NULL';
      } else {
         $sql .= ' NULL';
      }

      // Ajouter AUTO_INCREMENT si applicable
      if (isset($this->parameters['auto_increment']) && $this->parameters['auto_increment']) {
         switch ($driver) {
            case 'mysql':
               $sql .= ' AUTO_INCREMENT';
               break;
            case 'sqlite':
               // SQLite utilise AUTOINCREMENT avec INTEGER PRIMARY KEY
               // Généralement défini au niveau de la table
               break;
            case 'pgsql':
               // PostgreSQL utilise SERIAL au lieu de AUTO_INCREMENT
               // Déjà géré dans getSqlType()
               break;
         }
      }

      // Ajouter la valeur par défaut
      if ($this->hasDefault) {
         $defaultValue = $this->formatDefaultValue($this->default, $driver);
         $sql .= " DEFAULT $defaultValue";
      }

      // Ajouter un commentaire si applicable
      if ($this->comment !== null && $driver === 'mysql') {
         $sql .= " COMMENT '" . addslashes($this->comment) . "'";
      }


      if ($this->constrained !== null) {
         $sql .= " REFERENCES {$this->constrained[0]}({$this->constrained[1]})";
      }

      return $sql;
   }

   /**
    * Obtient le type SQL en fonction du driver de base de données
    *
    * @param string $driver Type de base de données
    * @return string
    */
   protected function getSqlType(string $driver): string
   {
      switch ($this->type) {
         case 'integer':
            return match ($driver) {
               'mysql' => 'INT',
               'sqlite' => 'INTEGER',
               'pgsql' => isset($this->parameters['auto_increment']) && $this->parameters['auto_increment'] ? 'SERIAL' : 'INTEGER',
               default => 'INTEGER'
            };
         case 'bigint':
            return match ($driver) {
               'mysql' => 'BIGINT',
               'sqlite' => 'INTEGER',
               'pgsql' => isset($this->parameters['auto_increment']) && $this->parameters['auto_increment'] ? 'BIGSERIAL' : 'BIGINT',
               default => 'BIGINT'
            };
         case 'varchar':
            $length = $this->parameters['length'] ?? 255;
            return match ($driver) {
               'mysql', 'pgsql' => "VARCHAR($length)",
               'sqlite' => 'TEXT',
               default => "VARCHAR($length)"
            };
         case 'text':
            return 'TEXT';
         case 'boolean':
            return match ($driver) {
               'mysql' => 'TINYINT(1)',
               'sqlite' => 'INTEGER',
               'pgsql' => 'BOOLEAN',
               default => 'BOOLEAN'
            };
         case 'date':
            return 'DATE';
         case 'datetime':
            return match ($driver) {
               'mysql' => 'DATETIME',
               'sqlite' => 'DATETIME',
               'pgsql' => 'TIMESTAMP',
               default => 'DATETIME'
            };
         case 'timestamp':
            return match ($driver) {
               'mysql' => 'TIMESTAMP',
               'sqlite' => 'DATETIME',
               'pgsql' => 'TIMESTAMP',
               default => 'TIMESTAMP'
            };
         case 'decimal':
            $precision = $this->parameters['precision'] ?? 8;
            $scale = $this->parameters['scale'] ?? 2;
            return "DECIMAL($precision, $scale)";
         case 'float':
            return match ($driver) {
               'mysql' => 'FLOAT',
               'sqlite' => 'REAL',
               'pgsql' => 'REAL',
               default => 'FLOAT'
            };
         default:
            return $this->type;
      }
   }

   /**
    * Formate la valeur par défaut pour SQL
    *
    * @param mixed $value Valeur à formater
    * @param string $driver Type de base de données
    * @return string
    */
   protected function formatDefaultValue($value, string $driver): string
   {
      if ($value === null) {
         return 'NULL';
      }

      if (is_bool($value)) {
         if ($driver === 'mysql') {
            return $value ? '1' : '0';
         } elseif ($driver === 'pgsql') {
            return $value ? 'TRUE' : 'FALSE';
         } else {
            return $value ? '1' : '0';
         }
      }

      if (is_numeric($value)) {
         return (string) $value;
      }

      return "'" . addslashes($value) . "'";
   }
}
