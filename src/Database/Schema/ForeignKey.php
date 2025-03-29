<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

/**
 * Classe représentant une clé étrangère dans un schéma de base de données
 */
class ForeignKey
{
   /**
    * Colonnes source pour la clé étrangère
    *
    * @var array
    */
   protected array $columns;

   /**
    * Table référencée par la clé étrangère
    *
    * @var string
    */
   protected string $refTable;

   /**
    * Colonnes référencées par la clé étrangère
    *
    * @var array
    */
   protected array $refColumns;

   /**
    * Nom de la contrainte de clé étrangère
    *
    * @var string
    */
   protected string $name;

   /**
    * Action à effectuer en cas de suppression de la ligne référencée
    *
    * @var string
    */
   protected string $onDelete = 'RESTRICT';

   /**
    * Action à effectuer en cas de mise à jour de la ligne référencée
    *
    * @var string
    */
   protected string $onUpdate = 'RESTRICT';

   /**
    * Constructeur
    *
    * @param array $columns Colonnes source
    * @param string $refTable Table référencée
    * @param array $refColumns Colonnes référencées
    * @param string $name Nom de la contrainte
    */
   public function __construct(array $columns, string $refTable, array $refColumns, string $name)
   {
      $this->columns = $columns;
      $this->refTable = $refTable;
      $this->refColumns = $refColumns;
      $this->name = $name;
   }

   /**
    * Définit l'action à effectuer en cas de suppression
    *
    * @param string $action Action ('CASCADE', 'RESTRICT', 'SET NULL', 'NO ACTION')
    * @return $this
    */
   public function onDelete(string $action): self
   {
      $this->onDelete = $this->validateAction($action);
      return $this;
   }

   /**
    * Définit l'action à effectuer en cas de mise à jour
    *
    * @param string $action Action ('CASCADE', 'RESTRICT', 'SET NULL', 'NO ACTION')
    * @return $this
    */
   public function onUpdate(string $action): self
   {
      $this->onUpdate = $this->validateAction($action);
      return $this;
   }

   /**
    * Définit l'action en cascade pour la suppression
    *
    * @return $this
    */
   public function cascadeOnDelete(): self
   {
      $this->onDelete = 'CASCADE';
      return $this;
   }

   /**
    * Définit l'action en cascade pour la mise à jour
    *
    * @return $this
    */
   public function cascadeOnUpdate(): self
   {
      $this->onUpdate = 'CASCADE';
      return $this;
   }

   /**
    * Définit l'action de mise à NULL pour la suppression
    *
    * @return $this
    */
   public function nullOnDelete(): self
   {
      $this->onDelete = 'SET NULL';
      return $this;
   }

   /**
    * Définit l'action de mise à NULL pour la mise à jour
    *
    * @return $this
    */
   public function nullOnUpdate(): self
   {
      $this->onUpdate = 'SET NULL';
      return $this;
   }

   /**
    * Convertit la clé étrangère en requête SQL
    *
    * @param string $table Table source
    * @param string $driver Type de base de données
    * @return string
    */
   public function toSql(string $table, string $driver): string
   {
      $columns = implode(', ', $this->columns);
      $refColumns = implode(', ', $this->refColumns);

      switch ($driver) {
         case 'mysql':
         case 'pgsql':
            return "ALTER TABLE {$table} ADD CONSTRAINT {$this->name} "
               . "FOREIGN KEY ({$columns}) REFERENCES {$this->refTable} ({$refColumns}) "
               . "ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
         case 'sqlite':
            // SQLite ne supporte pas l'ajout de contraintes de clé étrangère après la création de la table
            // Ceci nécessiterait une reconstruction complète de la table
            // Une simplification serait la suivante
            return "-- SQLite ne supporte pas l'ajout de contraintes de clé étrangère après la création de la table"
               . "-- Pour SQLite, les clés étrangères doivent être définies lors de la création de la table";
         default:
            return "ALTER TABLE {$table} ADD CONSTRAINT {$this->name} "
               . "FOREIGN KEY ({$columns}) REFERENCES {$this->refTable} ({$refColumns}) "
               . "ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
      }
   }

   /**
    * Valide et normalise l'action spécifiée
    *
    * @param string $action Action à valider
    * @return string
    */
   protected function validateAction(string $action): string
   {
      $action = strtoupper($action);
      $validActions = ['CASCADE', 'RESTRICT', 'SET NULL', 'NO ACTION'];

      if (!in_array($action, $validActions)) {
         throw new \InvalidArgumentException("Action invalide: {$action}. Les valeurs valides sont: " . implode(', ', $validActions));
      }

      return $action;
   }
}
