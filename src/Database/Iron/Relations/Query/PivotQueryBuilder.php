<?php

namespace IronFlow\Database\Iron\Relations\Query;

use IronFlow\Database\Collection;
use PDO;

/**
 * Classe utilitaire pour les requêtes sur la table pivot
 */
class PivotQueryBuilder
{
   /**
    * Instance PDO
    * 
    * @var PDO
    */
   protected PDO $pdo;

   /**
    * Nom de la table
    * 
    * @var string
    */
   protected string $table;

   /**
    * Colonnes à sélectionner
    * 
    * @var array
    */
   protected array $columns = ['*'];

   /**
    * Clauses WHERE
    * 
    * @var array
    */
   protected array $wheres = [];

   /**
    * Constructeur
    * 
    * @param PDO $pdo Instance PDO
    * @param string $table Nom de la table
    */
   public function __construct(PDO $pdo, string $table)
   {
      $this->pdo = $pdo;
      $this->table = $table;
   }

   /**
    * Définit les colonnes à sélectionner
    * 
    * @param array $columns Colonnes
    * @return $this
    */
   public function select(array $columns): self
   {
      $this->columns = $columns;
      return $this;
   }

   /**
    * Ajoute une clause WHERE
    * 
    * @param string $column Colonne
    * @param mixed $value Valeur
    * @return $this
    */
   public function where(string $column, $value): self
   {
      $this->wheres[] = [
         'column' => $column,
         'value' => $value
      ];

      return $this;
   }

   /**
    * Exécute la requête et retourne les résultats
    * 
    * @return Collection
    */
   public function get(): Collection
   {
      $query = "SELECT " . implode(', ', $this->columns) . " FROM {$this->table}";
      $bindings = [];

      if (!empty($this->wheres)) {
         $query .= " WHERE";

         foreach ($this->wheres as $i => $where) {
            $query .= ($i > 0 ? " AND " : " ") . "{$where['column']} = ?";
            $bindings[] = $where['value'];
         }
      }

      $stmt = $this->pdo->prepare($query);
      $stmt->execute($bindings);

      $results = [];
      while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
         $results[] = $row;
      }

      return new Collection($results);
   }

   /**
    * Mappe les résultats avec une fonction de callback et retourne une collection
    * 
    * @param callable $callback Fonction de callback
    * @return Collection
    */
   public function map(callable $callback): Collection
   {
      $results = $this->get();
      $mapped = [];

      foreach ($results as $item) {
         $mapped[] = $callback($item);
      }

      return new Collection($mapped);
   }

   /**
    * Mappe les résultats avec une fonction de callback et retourne une collection avec des clés personnalisées
    * 
    * @param callable $callback Fonction de callback qui retourne la clé et la valeur
    * @return Collection
    */
   public function mapWithKeys(callable $callback): Collection
   {
      $results = $this->get();
      $mapped = [];

      foreach ($results as $item) {
         $result = $callback($item);
         $mapped[key($result)] = reset($result);
      }

      return new Collection($mapped);
   }
}
