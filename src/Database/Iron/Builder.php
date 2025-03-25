<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron;

use IronFlow\Database\Collection;
use IronFlow\Database\Connection;
use IronFlow\Database\Model;
use PDO;

class Builder
{
   protected string $table;
   protected array $wheres = [];
   protected array $bindings = [];
   protected array $orders = [];
   protected ?int $limit = null;
   protected ?int $offset = null;
   protected array $joins = [];
   protected array $selects = ['*'];
   protected bool $distinct = false;
   protected array $groups = [];
   protected array $havings = [];
   protected PDO $connection;
   protected array $data = [];
   protected array $with = [];

   public function __construct(string $model)
   {
      $this->table = (new $model)->getTable();
      $this->connection = $this->getConnection();
   }

   public function getTable(): string
   {
      return $this->table;
   }

   protected function populate(array $data): self
   {
      $this->data = $data;
      return $this;
   }

   protected function toArray(): array
   {
      return $this->data;
   }

   public function select(array|string $columns): self
   {
      $this->selects = is_array($columns) ? $columns : func_get_args();
      return $this;
   }

   public function selectRaw(string $sql): self
   {
      $this->selects[] = $sql;
      return $this;
   }

   public function selectSub(string $sql, string $as): self
   {
      $this->selects[] = "($sql) as $as";
      return $this;
   }

   public function selectDistinct(array|string $columns): self
   {
      $this->selects = is_array($columns) ? $columns : func_get_args();
      $this->distinct = true;
      return $this;
   }

   public function insert(array $data): self
   {
      $this->data = $data;
      $this->bindings = $data;
      return $this;
   }

   public function update(array $data): self
   {
      $this->data = $data;
      $this->bindings = $data;
      return $this;
   }

   public function delete(): self
   {
      $this->data = [];
      return $this;
   }

   public function execute(): bool
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return $stmt->rowCount() > 0;
   }

   public function where(string $column, string $operator, $value = null): self
   {
      if ($value === null) {
         $value = $operator;
         $operator = '=';
      }

      $this->wheres[] = [
         'type' => 'basic',
         'column' => $column,
         'operator' => $operator,
         'value' => $value
      ];

      $this->bindings[] = $value;
      return $this;
   }

   public function orWhere(string $column, string $operator, $value = null): self
   {
      if ($value === null) {
         $value = $operator;
         $operator = '=';
      }

      $this->wheres[] = [
         'type' => 'or',
         'column' => $column,
         'operator' => $operator,
         'value' => $value
      ];

      $this->bindings[] = $value;
      return $this;
   }

   public function whereIn(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'in',
         'column' => $column,
         'values' => $values
      ];

      $this->bindings = array_merge($this->bindings, $values);
      return $this;
   }

   public function whereNull(string $column): self
   {
      $this->wheres[] = [
         'type' => 'null',
         'column' => $column
      ];
      return $this;
   }

   public function whereNotNull(string $column): self
   {
      $this->wheres[] = [
         'type' => 'not_null',
         'column' => $column
      ];
      return $this;
   }

   public function whereBetween(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'between',
         'column' => $column,
         'values' => $values
      ];
      return $this;
   }

   public function whereNotBetween(string $column, array $values): self
   {
      $this->wheres[] = [
         'type' => 'not_between',
         'column' => $column,
         'values' => $values
      ];
      return $this;
   }

   public function with(array $relations): self
   {
      $this->with = $relations;
      return $this;
   }

   public function from(string $table): self
   {
      $this->table = $table;
      return $this;
   }

   public function pluck(string $column): Collection
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return new Collection($stmt->fetchAll(PDO::FETCH_COLUMN, $column));
   }

   public function orderBy(string $column, string $direction = 'asc'): self
   {
      $this->orders[] = [
         'column' => $column,
         'direction' => strtolower($direction) === 'desc' ? 'desc' : 'asc'
      ];
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

   public function join(string $table, string $first, string $operator, string $second): self
   {
      $this->joins[] = [
         'table' => $table,
         'first' => $first,
         'operator' => $operator,
         'second' => $second,
         'type' => 'inner'
      ];
      return $this;
   }

   public function leftJoin(string $table, string $first, string $operator, string $second): self
   {
      $this->joins[] = [
         'table' => $table,
         'first' => $first,
         'operator' => $operator,
         'second' => $second,
         'type' => 'left'
      ];
      return $this;
   }

   public function groupBy(string|array $columns): self
   {
      $this->groups = is_array($columns) ? $columns : func_get_args();
      return $this;
   }

   public function having(string $column, string $operator, $value): self
   {
      $this->havings[] = [
         'column' => $column,
         'operator' => $operator,
         'value' => $value
      ];
      return $this;
   }

   public function get(): ?self
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return $stmt->fetchObject(static::class);
   }

   public function exists(): bool
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);

      return $stmt->rowCount() > 0;
   }

   public function first(): ?self
   {
      $this->limit(1);
      $results = $this->get();
      if ($results) {
         return $this->populate($results->toArray());
      }
      return null;
   }

   public function count(): int
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return (int) $stmt->fetchColumn();
   }

   protected function toSql(): string
   {
      $sql = ['SELECT ' . implode(', ', $this->selects) . ' FROM ' . $this->table];

      // Joins
      foreach ($this->joins as $join) {
         $sql[] = $join['type'] . ' JOIN ' . $join['table'] . ' ON ' . $join['first'] . ' ' . $join['operator'] . ' ' . $join['second'];
      }

      // Where clauses
      if (!empty($this->wheres)) {
         $sql[] = 'WHERE ' . $this->buildWhereClause();
      }

      // Group by
      if (!empty($this->groups)) {
         $sql[] = 'GROUP BY ' . implode(', ', $this->groups);
      }

      // Having
      if (!empty($this->havings)) {
         $sql[] = 'HAVING ' . $this->buildHavingClause();
      }

      // Order by
      if (!empty($this->orders)) {
         $sql[] = 'ORDER BY ' . $this->buildOrderByClause();
      }

      // Limit
      if ($this->limit !== null) {
         $sql[] = 'LIMIT ' . $this->limit;
      }

      // Offset
      if ($this->offset !== null) {
         $sql[] = 'OFFSET ' . $this->offset;
      }

      return implode(' ', $sql);
   }

   protected function buildWhereClause(): string
   {
      $clauses = [];
      foreach ($this->wheres as $where) {
         switch ($where['type']) {
            case 'basic':
               $clauses[] = $where['column'] . ' ' . $where['operator'] . ' ?';
               break;
            case 'or':
               $clauses[] = 'OR ' . $where['column'] . ' ' . $where['operator'] . ' ?';
               break;
            case 'in':
               $clauses[] = $where['column'] . ' IN (' . implode(',', array_fill(0, count($where['values']), '?')) . ')';
               break;
            case 'null':
               $clauses[] = $where['column'] . ' IS NULL';
               break;
            case 'not_null':
               $clauses[] = $where['column'] . ' IS NOT NULL';
               break;
         }
      }
      return implode(' ', $clauses);
   }

   protected function buildHavingClause(): string
   {
      $clauses = [];
      foreach ($this->havings as $having) {
         $clauses[] = $having['column'] . ' ' . $having['operator'] . ' ?';
      }
      return implode(' AND ', $clauses);
   }

   protected function buildOrderByClause(): string
   {
      $clauses = [];
      foreach ($this->orders as $order) {
         $clauses[] = $order['column'] . ' ' . $order['direction'];
      }
      return implode(', ', $clauses);
   }

   protected function getConnection(): PDO
   {
      return Connection::getInstance()->getConnection();
   }
}
