<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

/**
 * Classe représentant une clé étrangère
 */
class ForeignKey
{
   protected array $columns;
   protected string $referencedTable;
   protected array $referencedColumns;
   protected string $name;
   protected ?string $onDelete = null;
   protected ?string $onUpdate = null;

   public function __construct(
      array $columns,
      string $referencedTable,
      array $referencedColumns,
      string $name
   ) {
      $this->columns = $columns;
      $this->referencedTable = $referencedTable;
      $this->referencedColumns = $referencedColumns;
      $this->name = $name;
   }

   public function onDelete(string $action): self
   {
      $this->onDelete = $this->validateAction($action);
      return $this;
   }

   public function onUpdate(string $action): self
   {
      $this->onUpdate = $this->validateAction($action);
      return $this;
   }

   public function cascadeOnDelete(): self
   {
      return $this->onDelete('CASCADE');
   }

   public function cascadeOnUpdate(): self
   {
      return $this->onUpdate('CASCADE');
   }

   public function restrictOnDelete(): self
   {
      return $this->onDelete('RESTRICT');
   }

   public function nullOnDelete(): self
   {
      return $this->onDelete('SET NULL');
   }

   public function noActionOnDelete(): self
   {
      return $this->onDelete('NO ACTION');
   }

   public function toSql(string $table, string $driver): string
   {
      $columns = implode(', ', array_map(fn($col) => "`$col`", $this->columns));
      $refColumns = implode(', ', array_map(fn($col) => "`$col`", $this->referencedColumns));

      switch ($driver) {
         case 'mysql':
         case 'pgsql':
            $sql = "ALTER TABLE `{$table}` ADD CONSTRAINT `{$this->name}` "
               . "FOREIGN KEY ({$columns}) REFERENCES `{$this->referencedTable}` ({$refColumns})";

            if ($this->onDelete) {
               $sql .= " ON DELETE {$this->onDelete}";
            }
            if ($this->onUpdate) {
               $sql .= " ON UPDATE {$this->onUpdate}";
            }

            return $sql;

         case 'sqlite':
            return "-- SQLite ne supporte pas l'ajout de contraintes de clé étrangère après la création de la table.\n"
               . "-- Les clés étrangères doivent être définies lors de la création de la table.";

         default:
            throw new \InvalidArgumentException("Driver inconnu : {$driver}");
      }
   }

   public function getName(): string
   {
      return $this->name;
   }

   public function getColumns(): array
   {
      return $this->columns;
   }

   public function getReferencedTable(): string
   {
      return $this->referencedTable;
   }

   public function getReferencedColumns(): array
   {
      return $this->referencedColumns;
   }

   public function getOnDelete(): ?string
   {
      return $this->onDelete;
   }

   public function getOnUpdate(): ?string
   {
      return $this->onUpdate;
   }

   protected function validateAction(string $action): string
   {
      $action = strtoupper($action);
      $validActions = ['CASCADE', 'RESTRICT', 'SET NULL', 'NO ACTION'];

      if (!in_array($action, $validActions, true)) {
         throw new \InvalidArgumentException(
            "Action invalide : {$action}. Les valeurs valides sont : " . implode(', ', $validActions)
         );
      }

      return $action;
   }
}
