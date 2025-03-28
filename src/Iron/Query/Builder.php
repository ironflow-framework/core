<?php

declare(strict_types=1);

namespace IronFlow\Iron\Query;

use Exception;
use IronFlow\Iron\Collection;
use IronFlow\Iron\Connection;
use PDO;
use PDOException;
use PDOStatement;

class Builder
{
   protected string $modelClass;
   protected array $wheres = [];
   protected array $columns = ['*'];
   protected string $orderBy = '';
   protected string $orderDir = 'ASC';
   protected ?int $limit = null;
   protected ?int $offset = null;
   protected array $joins = [];
   protected array $bindings = [];
   protected array $groups = [];
   protected array $havings = [];
   protected array $eagerLoad = [];

   public function __construct(string $modelClass)
   {
      $this->modelClass = $modelClass;
   }

   public function select(array $columns = ['*']): self
   {
      $this->columns = $columns;
      return $this;
   }

   public function where(string $column, string $operator, $value): self
   {
      $this->wheres[] = [
         'type' => 'basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhere(string $column, string $operator, $value): self
   {
      $this->wheres[] = [
         'type' => 'basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function whereIn(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'in',
         'column' => $column,
         'values' => $values,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhereIn(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'in',
         'column' => $column,
         'values' => $values,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function whereNotIn(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'not_in',
         'column' => $column,
         'values' => $values,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhereNotIn(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'not_in',
         'column' => $column,
         'values' => $values,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function whereNull(string $column): self
   {
      $this->wheres[] = [
         'type' => 'null',
         'column' => $column,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhereNull(string $column): self
   {
      $this->wheres[] = [
         'type' => 'null',
         'column' => $column,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function whereNotNull(string $column): self
   {
      $this->wheres[] = [
         'type' => 'not_null',
         'column' => $column,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhereNotNull(string $column): self
   {
      $this->wheres[] = [
         'type' => 'not_null',
         'column' => $column,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function whereBetween(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'between',
         'column' => $column,
         'values' => $values,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhereBetween(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'between',
         'column' => $column,
         'values' => $values,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function whereNotBetween(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'not_between',
         'column' => $column,
         'values' => $values,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orWhereNotBetween(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'not_between',
         'column' => $column,
         'values' => $values,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
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

   public function leftJoin(string $table, string $first, string $operator, string $second): self
   {
      return $this->join($table, $first, $operator, $second, 'LEFT');
   }

   public function rightJoin(string $table, string $first, string $operator, string $second): self
   {
      return $this->join($table, $first, $operator, $second, 'RIGHT');
   }

   public function orderBy(string $column, string $direction = 'ASC'): self
   {
      $this->orderBy = $column;
      $this->orderDir = $direction;
      return $this;
   }

   public function limit(int $limit): self
   {
      $this->limit = $limit;
      return $this;
   }

   public function offset(int $offset): self
   {
      $this->offset = $offset;
      return $this;
   }

   public function get(): Collection
   {
      $sql = $this->toSql();
      $stmt = $this->execute($sql);
      $items = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
      return new Collection($items);
   }

   /**
    * Exécute la requête avec les relations chargées en eager loading
    * 
    * @return Collection
    */
   public function getWithRelations(): Collection
   {
      // Récupérer les résultats principaux
      $results = $this->get();

      // Si aucun résultat ou aucune relation à charger, retourner directement
      if ($results->isEmpty() || empty($this->eagerLoad)) {
         return $results;
      }

      // Charger chaque relation demandée
      foreach ($this->eagerLoad as $name => $constraints) {
         // Pour chaque modèle dans les résultats, charger cette relation
         $this->loadRelation($results, $name, $constraints);
      }

      return $results;
   }

   /**
    * Charge une relation pour une collection de modèles
    * 
    * @param Collection $models Collection de modèles
    * @param string $name Nom de la relation
    * @param \Closure|null $constraints Contraintes supplémentaires (callback)
    * @return void
    */
   protected function loadRelation(Collection $models, string $name, ?\Closure $constraints): void
   {
      if ($models->isEmpty()) {
         return;
      }

      // Prendre le premier modèle pour accéder à la relation
      $firstModel = $models->first();

      // Vérifier si la méthode de relation existe sur le modèle
      if (!method_exists($firstModel, $name)) {
         throw new Exception("Relation method [{$name}] does not exist on model.");
      }

      // Obtenir l'instance de relation
      $relation = $firstModel->$name();

      // Utiliser la relation pour charger les données associées pour tous les modèles
      $relation->initRelation($models, $name);
      $related = $relation->getEager($models);
      $relation->match($models, $related, $name);
   }

   public function first()
   {
      $this->limit(1);
      $results = $this->get();
      return $results->first();
   }

   /**
    * Ajoute des relations à charger en eager loading
    * 
    * @param array|string $relations Les relations à charger
    * @return self
    */
   public function with($relations): self
   {
      if (is_string($relations)) {
         $relations = func_get_args();
      }

      foreach ($relations as $name => $constraints) {
         // Si $constraints n'est pas un Closure, cela signifie qu'il s'agit simplement d'une relation sans contraintes
         if (is_numeric($name)) {
            [$name, $constraints] = [$constraints, null];
         }

         $this->eagerLoad[$name] = $constraints;
      }

      return $this;
   }

   public function toSql(): string
   {
      $table = (new $this->modelClass())->getTable();

      $sql = "SELECT " . $this->buildSelect();
      $sql .= " FROM {$table}";

      // Ajouter les jointures
      foreach ($this->joins as $join) {
         $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
      }

      // Ajouter les conditions WHERE
      if (!empty($this->wheres)) {
         $sql .= " WHERE " . $this->buildWhereClause();
      }

      // Ajouter GROUP BY
      if (!empty($this->groups)) {
         $sql .= " GROUP BY " . implode(', ', $this->groups);
      }

      // Ajouter HAVING
      if (!empty($this->havings)) {
         $sql .= " HAVING " . $this->buildHavingClause();
      }

      // Ajouter ORDER BY
      if (!empty($this->orderBy)) {
         $sql .= " ORDER BY {$this->orderBy} {$this->orderDir}";
      }

      // Ajouter LIMIT et OFFSET
      if (!is_null($this->limit)) {
         $sql .= " LIMIT {$this->limit}";

         if (!is_null($this->offset)) {
            $sql .= " OFFSET {$this->offset}";
         }
      }

      return $sql;
   }

   protected function buildSelect(): string
   {
      return implode(', ', $this->columns);
   }

   protected function buildWhereClause(): string
   {
      $whereClause = '';

      foreach ($this->wheres as $index => $where) {
         // Ajouter l'opérateur booléen (AND/OR) sauf pour la première condition
         if ($index > 0) {
            $whereClause .= " {$where['boolean']} ";
         }

         switch ($where['type']) {
            case 'basic':
               $placeholder = ':where_' . count($this->bindings);
               $this->bindings[$placeholder] = $where['value'];
               $whereClause .= "{$where['column']} {$where['operator']} {$placeholder}";
               break;

            case 'in':
               $placeholders = [];
               foreach ($where['values'] as $value) {
                  $placeholder = ':where_' . count($this->bindings);
                  $this->bindings[$placeholder] = $value;
                  $placeholders[] = $placeholder;
               }
               $whereClause .= "{$where['column']} IN (" . implode(', ', $placeholders) . ")";
               break;

            case 'not_in':
               $placeholders = [];
               foreach ($where['values'] as $value) {
                  $placeholder = ':where_' . count($this->bindings);
                  $this->bindings[$placeholder] = $value;
                  $placeholders[] = $placeholder;
               }
               $whereClause .= "{$where['column']} NOT IN (" . implode(', ', $placeholders) . ")";
               break;

            case 'null':
               $whereClause .= "{$where['column']} IS NULL";
               break;

            case 'not_null':
               $whereClause .= "{$where['column']} IS NOT NULL";
               break;

            case 'between':
               $placeholder1 = ':where_' . count($this->bindings);
               $this->bindings[$placeholder1] = $where['values'][0];
               $placeholder2 = ':where_' . count($this->bindings);
               $this->bindings[$placeholder2] = $where['values'][1];
               $whereClause .= "{$where['column']} BETWEEN {$placeholder1} AND {$placeholder2}";
               break;

            case 'not_between':
               $placeholder1 = ':where_' . count($this->bindings);
               $this->bindings[$placeholder1] = $where['values'][0];
               $placeholder2 = ':where_' . count($this->bindings);
               $this->bindings[$placeholder2] = $where['values'][1];
               $whereClause .= "{$where['column']} NOT BETWEEN {$placeholder1} AND {$placeholder2}";
               break;
         }
      }

      return $whereClause;
   }

   protected function buildHavingClause(): string
   {
      // Implementation similaire à buildWhereClause pour les conditions HAVING
      return '';
   }

   protected function getBindings(): array
   {
      return $this->bindings;
   }

   protected function execute(string $sql): PDOStatement
   {
      $conn = Connection::getInstance()->getConnection();

      try {
         $stmt = $conn->prepare($sql);

         foreach ($this->bindings as $key => $value) {
            $stmt->bindValue($key, $value);
         }

         $stmt->execute();
         return $stmt;
      } catch (PDOException $e) {
         throw new Exception("Error executing query: " . $e->getMessage());
      }
   }

   public function groupBy(string ...$columns): self
   {
      $this->groups = array_merge($this->groups, $columns);
      return $this;
   }

   public function having(string $column, string $operator, $value): self
   {
      $this->havings[] = [
         'type' => 'basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => 'AND'
      ];
      return $this;
   }

   public function orHaving(string $column, string $operator, $value): self
   {
      $this->havings[] = [
         'type' => 'basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value,
         'boolean' => 'OR'
      ];
      return $this;
   }

   public function count(): int
   {
      $table = (new $this->modelClass())->getTable();

      $sql = "SELECT COUNT(*) FROM {$table}";

      // Ajouter les jointures
      foreach ($this->joins as $join) {
         $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
      }

      // Ajouter les conditions WHERE
      if (!empty($this->wheres)) {
         $sql .= " WHERE " . $this->buildWhereClause();
      }

      // Ajouter GROUP BY
      if (!empty($this->groups)) {
         $sql .= " GROUP BY " . implode(', ', $this->groups);
      }

      // Ajouter HAVING
      if (!empty($this->havings)) {
         $sql .= " HAVING " . $this->buildHavingClause();
      }

      $stmt = $this->execute($sql);
      return (int) $stmt->fetchColumn();
   }

   public function exists(): bool
   {
      $table = (new $this->modelClass())->getTable();

      $sql = "SELECT EXISTS (SELECT 1 FROM {$table}";

      // Ajouter les jointures
      foreach ($this->joins as $join) {
         $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
      }

      // Ajouter les conditions WHERE
      if (!empty($this->wheres)) {
         $sql .= " WHERE " . $this->buildWhereClause();
      }

      $sql .= ")";

      $stmt = $this->execute($sql);
      return (bool) $stmt->fetchColumn();
   }

   public function paginate(int $perPage = 15, int $page = 1): array
   {
      $total = $this->count();

      $this->limit($perPage);
      $this->offset(($page - 1) * $perPage);

      $results = $this->get();

      return [
         'data' => $results,
         'total' => $total,
         'per_page' => $perPage,
         'current_page' => $page,
         'last_page' => ceil($total / $perPage)
      ];
   }
}
