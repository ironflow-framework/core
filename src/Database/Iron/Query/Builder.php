<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Query;

use Exception;
use IronFlow\Database\Collection;
use IronFlow\Database\Connection;
use IronFlow\Database\Model;
use PDO;
use PDOException;

/**
 * Builder de requêtes SQL
 * 
 * Cette classe permet de construire des requêtes SQL de manière fluide et orientée objet.
 */
class Builder
{
   /**
    * Instance du modèle associé
    * 
    * @var Model
    */
   protected Model $model;

   /**
    * Table de la requête
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
    * Clauses WHERE de la requête
    * 
    * @var array
    */
   protected array $wheres = [];

   /**
    * Clauses ORDER BY de la requête
    * 
    * @var array
    */
   protected array $orders = [];

   /**
    * Relations à charger avec la requête
    * 
    * @var array
    */
   protected array $with = [];

   /**
    * Limite de résultats
    * 
    * @var int|null
    */
   protected ?int $limit = null;

   /**
    * Décalage des résultats
    * 
    * @var int|null
    */
   protected ?int $offset = null;

   /**
    * Clauses JOIN de la requête
    * 
    * @var array
    */
   protected array $joins = [];

   /**
    * Clauses GROUP BY de la requête
    * 
    * @var array
    */
   protected array $groups = [];

   /**
    * Clauses HAVING de la requête
    * 
    * @var array
    */
   protected array $havings = [];

   /**
    * Constructeur
    * 
    * @param Model $model Modèle associé à la requête
    */
   public function __construct(Model $model)
   {
      $this->model = $model;
      $this->table = $model->getTable();
   }

   /**
    * Définit les colonnes à sélectionner
    * 
    * @param array|string $columns Colonnes
    * @return self
    */
   public function select($columns = ['*']): self
   {
      $this->columns = is_array($columns) ? $columns : func_get_args();
      return $this;
   }

   /**
    * Ajoute une clause WHERE à la requête
    * 
    * @param string $column Colonne
    * @param mixed $operator Opérateur ou valeur
    * @param mixed|null $value Valeur (optionnelle)
    * @param string $boolean Opérateur booléen (AND/OR)
    * @return self
    */
   public function where(string $column, $operator = null, $value = null, string $boolean = 'AND'): self
   {
      // Si seulement deux arguments sont fournis, le second est la valeur et l'opérateur est '='
      if ($value === null) {
         $value = $operator;
         $operator = '=';
      }

      $this->wheres[] = [
         'type' => 'basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => $boolean
      ];

      return $this;
   }

   /**
    * Ajoute une clause WHERE avec opérateur OR
    * 
    * @param string $column Colonne
    * @param mixed $operator Opérateur ou valeur
    * @param mixed|null $value Valeur
    * @return self
    */
   public function orWhere(string $column, $operator = null, $value = null): self
   {
      return $this->where($column, $operator, $value, 'OR');
   }

   /**
    * Ajoute une clause WHERE IN
    * 
    * @param string $column Colonne
    * @param array $values Valeurs
    * @param string $boolean Opérateur booléen (AND/OR)
    * @param bool $not Négation
    * @return self
    */
   public function whereIn(string $column, array $values, string $boolean = 'AND', bool $not = false): self
   {
      $this->wheres[] = [
         'type' => 'in',
         'column' => $column,
         'values' => $values,
         'boolean' => $boolean,
         'not' => $not
      ];

      return $this;
   }

   /**
    * Ajoute une clause WHERE NOT IN
    * 
    * @param string $column Colonne
    * @param array $values Valeurs
    * @param string $boolean Opérateur booléen (AND/OR)
    * @return self
    */
   public function whereNotIn(string $column, array $values, string $boolean = 'AND'): self
   {
      return $this->whereIn($column, $values, $boolean, true);
   }

   /**
    * Ajoute une clause WHERE NULL
    * 
    * @param string $column Colonne
    * @param string $boolean Opérateur booléen (AND/OR)
    * @param bool $not Négation
    * @return self
    */
   public function whereNull(string $column, string $boolean = 'AND', bool $not = false): self
   {
      $this->wheres[] = [
         'type' => 'null',
         'column' => $column,
         'boolean' => $boolean,
         'not' => $not
      ];

      return $this;
   }

   /**
    * Ajoute une clause WHERE NOT NULL
    * 
    * @param string $column Colonne
    * @param string $boolean Opérateur booléen (AND/OR)
    * @return self
    */
   public function whereNotNull(string $column, string $boolean = 'AND'): self
   {
      return $this->whereNull($column, $boolean, true);
   }

   /**
    * Ajoute une clause ORDER BY
    * 
    * @param string $column Colonne
    * @param string $direction Direction (asc/desc)
    * @return self
    */
   public function orderBy(string $column, string $direction = 'asc'): self
   {
      $direction = strtolower($direction);

      if (!in_array($direction, ['asc', 'desc'])) {
         $direction = 'asc';
      }

      $this->orders[] = [
         'column' => $column,
         'direction' => $direction
      ];

      return $this;
   }

   /**
    * Définit une limite de résultats
    * 
    * @param int $limit Limite
    * @return self
    */
   public function limit(int $limit): self
   {
      $this->limit = $limit;
      return $this;
   }

   /**
    * Définit un décalage de résultats
    * 
    * @param int $offset Décalage
    * @return self
    */
   public function offset(int $offset): self
   {
      $this->offset = $offset;
      return $this;
   }

   /**
    * Pagine les résultats
    * 
    * @param int $page Numéro de page
    * @param int $perPage Nombre d'éléments par page
    * @return self
    */
   public function forPage(int $page, int $perPage): self
   {
      return $this->offset(($page - 1) * $perPage)->limit($perPage);
   }

   /**
    * Ajoute une clause JOIN
    * 
    * @param string $table Table à joindre
    * @param string $first Première colonne de jointure
    * @param string $operator Opérateur de jointure
    * @param string $second Seconde colonne de jointure
    * @param string $type Type de jointure
    * @return self
    */
   public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): self
   {
      $this->joins[] = [
         'table' => $table,
         'first' => $first,
         'operator' => $operator,
         'second' => $second,
         'type' => $type
      ];

      return $this;
   }

   /**
    * Ajoute une clause LEFT JOIN
    * 
    * @param string $table Table à joindre
    * @param string $first Première colonne de jointure
    * @param string $operator Opérateur de jointure
    * @param string $second Seconde colonne de jointure
    * @return self
    */
   public function leftJoin(string $table, string $first, string $operator, string $second): self
   {
      return $this->join($table, $first, $operator, $second, 'left');
   }

   /**
    * Ajoute une clause RIGHT JOIN
    * 
    * @param string $table Table à joindre
    * @param string $first Première colonne de jointure
    * @param string $operator Opérateur de jointure
    * @param string $second Seconde colonne de jointure
    * @return self
    */
   public function rightJoin(string $table, string $first, string $operator, string $second): self
   {
      return $this->join($table, $first, $operator, $second, 'right');
   }

   /**
    * Ajoute une clause GROUP BY
    * 
    * @param string|array $columns Colonnes
    * @return self
    */
   public function groupBy($columns): self
   {
      $this->groups = is_array($columns) ? $columns : func_get_args();
      return $this;
   }

   /**
    * Ajoute une clause HAVING
    * 
    * @param string $column Colonne
    * @param string $operator Opérateur
    * @param mixed $value Valeur
    * @param string $boolean Opérateur booléen (AND/OR)
    * @return self
    */
   public function having(string $column, string $operator, $value, string $boolean = 'AND'): self
   {
      $this->havings[] = [
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => $boolean
      ];

      return $this;
   }

   /**
    * Définit les relations à charger avec la requête
    * 
    * @param array|string $relations Relations
    * @return self
    */
   public function with($relations): self
   {
      $this->with = is_array($relations) ? $relations : func_get_args();
      return $this;
   }

   /**
    * Construit et retourne la requête SQL
    * 
    * @return string
    */
   protected function toSql(): string
   {
      $query = "SELECT " . $this->compileColumns();
      $query .= " FROM {$this->table}";

      // Ajouter les jointures
      if (!empty($this->joins)) {
         $query .= $this->compileJoins();
      }

      // Ajouter les clauses WHERE
      if (!empty($this->wheres)) {
         $query .= $this->compileWheres();
      }

      // Ajouter les clauses GROUP BY
      if (!empty($this->groups)) {
         $query .= $this->compileGroups();
      }

      // Ajouter les clauses HAVING
      if (!empty($this->havings)) {
         $query .= $this->compileHavings();
      }

      // Ajouter les clauses ORDER BY
      if (!empty($this->orders)) {
         $query .= $this->compileOrders();
      }

      // Ajouter la limite et le décalage
      if ($this->limit !== null) {
         $query .= " LIMIT {$this->limit}";

         if ($this->offset !== null) {
            $query .= " OFFSET {$this->offset}";
         }
      }

      return $query;
   }

   /**
    * Compile la liste des colonnes
    * 
    * @return string
    */
   protected function compileColumns(): string
   {
      return implode(', ', $this->columns);
   }

   /**
    * Compile les clauses JOIN
    * 
    * @return string
    */
   protected function compileJoins(): string
   {
      $sql = '';

      foreach ($this->joins as $join) {
         $type = strtoupper($join['type']);
         $table = $join['table'];
         $first = $join['first'];
         $operator = $join['operator'];
         $second = $join['second'];

         $sql .= " {$type} JOIN {$table} ON {$first} {$operator} {$second}";
      }

      return $sql;
   }

   /**
    * Compile les clauses WHERE
    * 
    * @return string
    */
   protected function compileWheres(): string
   {
      if (empty($this->wheres)) {
         return '';
      }

      $sql = ' WHERE';

      foreach ($this->wheres as $i => $where) {
         $boolean = $i === 0 ? '' : " {$where['boolean']}";

         switch ($where['type']) {
            case 'basic':
               $sql .= "{$boolean} {$where['column']} {$where['operator']} ?";
               break;
            case 'in':
               $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
               $not = $where['not'] ? ' NOT' : '';
               $sql .= "{$boolean} {$where['column']}{$not} IN ({$placeholders})";
               break;
            case 'null':
               $not = $where['not'] ? ' NOT' : '';
               $sql .= "{$boolean} {$where['column']} IS{$not} NULL";
               break;
         }
      }

      return $sql;
   }

   /**
    * Compile les clauses GROUP BY
    * 
    * @return string
    */
   protected function compileGroups(): string
   {
      return ' GROUP BY ' . implode(', ', $this->groups);
   }

   /**
    * Compile les clauses HAVING
    * 
    * @return string
    */
   protected function compileHavings(): string
   {
      if (empty($this->havings)) {
         return '';
      }

      $sql = ' HAVING';

      foreach ($this->havings as $i => $having) {
         $boolean = $i === 0 ? '' : " {$having['boolean']}";
         $sql .= "{$boolean} {$having['column']} {$having['operator']} ?";
      }

      return $sql;
   }

   /**
    * Compile les clauses ORDER BY
    * 
    * @return string
    */
   protected function compileOrders(): string
   {
      if (empty($this->orders)) {
         return '';
      }

      $sql = ' ORDER BY';

      foreach ($this->orders as $i => $order) {
         $sql .= ($i === 0 ? ' ' : ', ') . "{$order['column']} {$order['direction']}";
      }

      return $sql;
   }

   /**
    * Extrait les valeurs des clauses WHERE
    * 
    * @return array
    */
   protected function getBindings(): array
   {
      $bindings = [];

      // Extraire les valeurs des clauses WHERE
      foreach ($this->wheres as $where) {
         switch ($where['type']) {
            case 'basic':
               $bindings[] = $where['value'];
               break;
            case 'in':
               $bindings = array_merge($bindings, $where['values']);
               break;
         }
      }

      // Extraire les valeurs des clauses HAVING
      foreach ($this->havings as $having) {
         $bindings[] = $having['value'];
      }

      return $bindings;
   }

   /**
    * Exécute la requête et retourne tous les résultats
    * 
    * @return Collection
    */
   public function get(): Collection
   {
      $sql = $this->toSql();
      $bindings = $this->getBindings();

      try {
         $stmt = Connection::getInstance()->getConnection()->prepare($sql);
         $stmt->execute($bindings);
         $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

         $models = [];
         $modelClass = get_class($this->model);

         foreach ($results as $attributes) {
            $model = new $modelClass();
            $model->fill($attributes);

            // Charger les relations si nécessaire
            if (!empty($this->with)) {
               $this->loadRelations($model);
            }

            $models[] = $model;
         }

         return new Collection($models);
      } catch (PDOException $e) {
         throw new Exception("Erreur d'exécution de la requête: " . $e->getMessage());
      }
   }

   /**
    * Récupère le premier résultat
    * 
    * @return Model|null
    */
   public function first(): ?Model
   {
      $this->limit(1);
      $result = $this->get();

      return $result->first();
   }

   /**
    * Trouve un enregistrement par son identifiant
    * 
    * @param mixed $id Identifiant
    * @return Model|null
    */
   public function find($id): ?Model
   {
      return $this->where($this->model->getKeyName(), $id)->first();
   }

   /**
    * Exécute une requête d'agrégation
    * 
    * @param string $function Fonction d'agrégation
    * @param string $column Colonne
    * @return mixed
    */
   protected function aggregate(string $function, string $column = '*')
   {
      $this->columns = ["{$function}({$column}) as aggregate"];

      $sql = $this->toSql();
      $bindings = $this->getBindings();

      try {
         $stmt = Connection::getInstance()->getConnection()->prepare($sql);
         $stmt->execute($bindings);
         $result = $stmt->fetch(PDO::FETCH_ASSOC);

         if (!$result) {
            return 0;
         }

         $value = $result['aggregate'];

         return is_numeric($value) ? (int) $value : $value;
      } catch (PDOException $e) {
         throw new Exception("Erreur d'exécution de la requête d'agrégation: " . $e->getMessage());
      }
   }

   /**
    * Compte le nombre d'enregistrements
    * 
    * @return int
    */
   public function count(string $columns = '*'): int
   {
      return $this->aggregate('COUNT', $columns);
   }

   /**
    * Calcule la somme d'une colonne
    * 
    * @param string $column Colonne
    * @return int
    */
   public function sum(string $column): int
   {
      return $this->aggregate('SUM', $column);
   }

   /**
    * Calcule la moyenne d'une colonne
    * 
    * @param string $column Colonne
    * @return float
    */
   public function avg(string $column): float
   {
      return $this->aggregate('AVG', $column);
   }

   /**
    * Récupère la valeur minimale d'une colonne
    * 
    * @param string $column Colonne
    * @return mixed
    */
   public function min(string $column)
   {
      return $this->aggregate('MIN', $column);
   }

   /**
    * Récupère la valeur maximale d'une colonne
    * 
    * @param string $column Colonne
    * @return mixed
    */
   public function max(string $column)
   {
      return $this->aggregate('MAX', $column);
   }

   /**
    * Charge les relations d'un modèle
    * 
    * @param Model $model Modèle
    */
   protected function loadRelations(Model $model): void
   {
      foreach ($this->with as $relation) {
         if ($model->isRelation($relation)) {
            $model->setRelation($relation, $model->$relation()->get());
         }
      }
   }

   /**
    * Retourne si des données existent
    * @return bool
    */
   public function exists(): bool
   {

      $sql = "SELECT EXISTS (SELECT 1 FROM {$this->table}";

      // Ajouter les jointures
      foreach ($this->joins as $join) {
         $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
      }

      // Ajouter les conditions WHERE
      if (!empty($this->wheres)) {
         $sql .= " WHERE " . $this->compileWheres();
      }

      $sql .= ")";

      $stmt = Connection::getInstance()->getConnection()->prepare($sql);
      $stmt->execute();
      return (bool) $stmt->fetchColumn();
   }


   /**
    * Insère un nouvel enregistrement
    * 
    * @param array $values Valeurs
    * @return bool
    */
   public function insert(array $values): bool
   {
      if (empty($values)) {
         return true;
      }

      // Gérer les tableaux multidimensionnels
      if (!is_array(reset($values))) {
         $values = [$values];
      }

      // Récupérer les colonnes
      $columns = array_keys(reset($values));

      // Préparer les placeholders pour toutes les lignes
      $placeholders = [];
      $allBindings = [];

      foreach ($values as $record) {
         $recordPlaceholders = [];

         foreach ($columns as $column) {
            $recordPlaceholders[] = '?';
            $allBindings[] = $record[$column] ?? null;
         }

         $placeholders[] = '(' . implode(', ', $recordPlaceholders) . ')';
      }

      // Construire la requête
      $columnList = implode(', ', $columns);
      $placeholderList = implode(', ', $placeholders);

      $query = "INSERT INTO {$this->table} ({$columnList}) VALUES {$placeholderList}";

      try {
         $stmt = Connection::getInstance()->getConnection()->prepare($query);
         return $stmt->execute($allBindings);
      } catch (PDOException $e) {
         throw new Exception("Erreur d'insertion: " . $e->getMessage());
      }
   }

   /**
    * Met à jour les enregistrements correspondants
    * 
    * @param array $values Valeurs à mettre à jour
    * @return int Nombre d'enregistrements mis à jour
    */
   public function update(array $values): int
   {
      // Préparer les assignments "colonne = ?"
      $sets = [];
      $bindings = [];

      foreach ($values as $column => $value) {
         $sets[] = "{$column} = ?";
         $bindings[] = $value;
      }

      // Ajouter les bindings des clauses WHERE
      $bindings = array_merge($bindings, $this->getBindings());

      // Construire la requête
      $query = "UPDATE {$this->table} SET " . implode(', ', $sets);

      // Ajouter les clauses WHERE
      if (!empty($this->wheres)) {
         $query .= $this->compileWheres();
      }

      try {
         $stmt = Connection::getInstance()->getConnection()->prepare($query);
         $stmt->execute($bindings);
         return $stmt->rowCount();
      } catch (PDOException $e) {
         throw new Exception("Erreur de mise à jour: " . $e->getMessage());
      }
   }

   /**
    * Supprime les enregistrements correspondants
    * 
    * @return int Nombre d'enregistrements supprimés
    */
   public function delete(): int
   {
      $query = "DELETE FROM {$this->table}";

      // Ajouter les clauses WHERE
      if (!empty($this->wheres)) {
         $query .= $this->compileWheres();
      }

      try {
         $stmt = Connection::getInstance()->getConnection()->prepare($query);
         $stmt->execute($this->getBindings());
         return $stmt->rowCount();
      } catch (PDOException $e) {
         throw new Exception("Erreur de suppression: " . $e->getMessage());
      }
   }
}
