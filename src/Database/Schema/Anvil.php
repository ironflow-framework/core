<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;


class Anvil
{
   protected array $columns = [];
   protected array $indexes = [];
   protected array $foreignKeys = [];
   protected string $table;
   protected bool $ifExists = false;
   protected bool $ifNotExists = false;

   public function __construct(string $table)
   {
      $this->table = $table;
   }

   public function getIfTableExists(): bool{
      return $this->ifExists;
   }

   public function setIfTableExists(bool $newValue): void
   {
      $this->ifExists = $newValue;
   }

   public function column(string $name, string $type, array $options = []): self
   {
      $this->columns[] = [
         'name' => $name,
         'type' => $type,
         'options' => $options
      ];
      return $this;
   }

   public function id(): self
   {
      return $this->column('id', 'bigint', [
         'unsigned' => true,
         'auto_increment' => true,
         'primary' => true
      ]);
   }

   public function string(string $name, int $length = 255): self
   {
      return $this->column($name, "varchar({$length})");
   }

   public function text(string $name): self
   {
      return $this->column($name, 'text');
   }

   public function integer(string $name, bool $autoIncrement = false, bool $unsigned = false): self
   {
      $options = [];
      if ($autoIncrement) {
         $options['auto_increment'] = true;
      }
      if ($unsigned) {
         $options['unsigned'] = true;
      }
      return $this->column($name, 'int', $options);
   }

   public function bigInteger(string $name, bool $autoIncrement = false, bool $unsigned = false): self
   {
      $options = [];
      if ($autoIncrement) {
         $options['auto_increment'] = true;
      }
      if ($unsigned) {
         $options['unsigned'] = true;
      }
      return $this->column($name, 'bigint', $options);
   }

   public function float(string $name, int $precision = 8, int $scale = 2): self
   {
      return $this->column($name, "float({$precision},{$scale})");
   }

   public function decimal(string $name, int $precision = 8, int $scale = 2): self
   {
      return $this->column($name, "decimal({$precision},{$scale})");
   }

   public function boolean(string $name): self
   {
      return $this->column($name, 'tinyint', ['length' => 1]);
   }

   public function date(string $name): self
   {
      return $this->column($name, 'date');
   }

   public function dateTime(string $name): self
   {
      return $this->column($name, 'datetime');
   }

   public function timestamp(string $name): self
   {
      return $this->column($name, 'timestamp');
   }

   public function timestamps(): self
   {
      $this->timestamp('created_at')->nullable();
      $this->timestamp('updated_at')->nullable();
      return $this;
   }

   public function softDeletes(): self
   {
      return $this->timestamp('deleted_at')->nullable();
   }

   public function nullable(): self
   {
      $this->columns[count($this->columns) - 1]['options']['nullable'] = true;
      return $this;
   }

   public function default($value): self
   {
      $this->columns[count($this->columns) - 1]['options']['default'] = $value;
      return $this;
   }

   public function unique(): self
   {
      $this->columns[count($this->columns) - 1]['options']['unique'] = true;
      return $this;
   }

   public function primary(): self
   {
      $this->columns[count($this->columns) - 1]['options']['primary'] = true;
      return $this;
   }

   public function index(string $name, array|string $columns, string $type = 'index'): self
   {
      $this->indexes[] = [
         'name' => $name,
         'columns' => is_array($columns) ? $columns : [$columns],
         'type' => $type
      ];
      return $this;
   }

   public function foreignKey(string $name, array|string $columns): self
   {
      $this->foreignKeys[] = [
         'name' => $name,
         'columns' => is_array($columns) ? $columns : [$columns]
      ];
      return $this;
   }

   public function references(string $column): self
   {
      $this->foreignKeys[count($this->foreignKeys) - 1]['references'] = $column;
      return $this;
   }

   public function on(string $table): self
   {
      $this->foreignKeys[count($this->foreignKeys) - 1]['on'] = $table;
      return $this;
   }

   public function onDelete(string $action): self
   {
      $this->foreignKeys[count($this->foreignKeys) - 1]['onDelete'] = $action;
      return $this;
   }

   public function onUpdate(string $action): self
   {
      $this->foreignKeys[count($this->foreignKeys) - 1]['onUpdate'] = $action;
      return $this;
   }

   public function buildCreateTableSql(): string
   {
      $sql = ['CREATE TABLE'];

      if ($this->ifNotExists) {
         $sql[] = 'IF NOT EXISTS';
      }

      $sql[] = $this->table;
      $sql[] = '(';

      $columns = [];
      foreach ($this->columns as $column) {
         $columns[] = $this->buildColumnSql($column);
      }

      foreach ($this->indexes as $index) {
         $columns[] = $this->buildIndexSql($index);
      }

      foreach ($this->foreignKeys as $foreignKey) {
         $columns[] = $this->buildForeignKeySql($foreignKey);
      }

      $sql[] = implode(', ', $columns);
      $sql[] = ')';

      return implode(' ', $sql);
   }

   public function buildAlterTableSql(): string
   {
      $sql = ['ALTER TABLE'];

      if ($this->ifNotExists) {
         $sql[] = 'IF NOT EXISTS';
      }

      $sql[] = $this->table;
      $sql[] = '(';

      $columns = [];
      foreach ($this->columns as $column) {
         $columns[] = $this->buildColumnSql($column);
      }

      foreach ($this->indexes as $index) {
         $columns[] = $this->buildIndexSql($index);
      }

      foreach ($this->foreignKeys as $foreignKey) {
         $columns[] = $this->buildForeignKeySql($foreignKey);
      }

      $sql[] = implode(', ', $columns);
      $sql[] = ')';

      return implode(' ', $sql);
   }

   public function buildDropTableSql(): string
   {
      $sql = ['DROP TABLE'];

      if ($this->ifExists) {
         $sql[] = 'IF EXISTS';
      }

      $sql[] = $this->table;

      return implode(' ', $sql);
   }

   protected function buildColumnSql(array $column): string
   {
      $sql = [
         $column['name'],
         $column['type']
      ];

      if (isset($column['options']['length'])) {
         $sql[1] .= "({$column['options']['length']})";
      }

      if (isset($column['options']['unsigned']) && $column['options']['unsigned']) {
         $sql[1] .= ' UNSIGNED';
      }

      if (!isset($column['options']['nullable']) || !$column['options']['nullable']) {
         $sql[] = 'NOT NULL';
      }

      if (isset($column['options']['default'])) {
         $default = $column['options']['default'];
         if (is_string($default)) {
            $default = "'{$default}'";
         }
         $sql[] = "DEFAULT {$default}";
      }

      if (isset($column['options']['auto_increment']) && $column['options']['auto_increment']) {
         $sql[] = 'AUTO_INCREMENT';
      }

      if (isset($column['options']['primary']) && $column['options']['primary']) {
         $sql[] = 'PRIMARY KEY';
      }

      if (isset($column['options']['unique']) && $column['options']['unique']) {
         $sql[] = 'UNIQUE';
      }

      return implode(' ', $sql);
   }

   protected function buildIndexSql(array $index): string
   {
      $sql = [];

      switch ($index['type']) {
         case 'unique':
            $sql[] = 'UNIQUE INDEX';
            break;
         case 'fulltext':
            $sql[] = 'FULLTEXT INDEX';
            break;
         case 'spatial':
            $sql[] = 'SPATIAL INDEX';
            break;
         default:
            $sql[] = 'INDEX';
      }

      $sql[] = $index['name'];
      $sql[] = '(' . implode(', ', $index['columns']) . ')';

      return implode(' ', $sql);
   }

   protected function buildForeignKeySql(array $foreignKey): string
   {
      $sql = [
         'CONSTRAINT',
         $foreignKey['name'],
         'FOREIGN KEY',
         '(' . implode(', ', $foreignKey['columns']) . ')',
         'REFERENCES',
         $foreignKey['on'],
         '(' . $foreignKey['references'] . ')'
      ];

      if (isset($foreignKey['onDelete'])) {
         $sql[] = 'ON DELETE ' . $foreignKey['onDelete'];
      }

      if (isset($foreignKey['onUpdate'])) {
         $sql[] = 'ON UPDATE ' . $foreignKey['onUpdate'];
      }

      return implode(' ', $sql);
   }
}
