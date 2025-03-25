<?php

declare(strict_types=1);

namespace IronFlow\Database\Query;

use IronFlow\Database\Connection;
use PDO;

class Query
{
   protected Connection $connection;
   protected string $table;
   protected array $selects = ['*'];
   protected array $joins = [];
   protected array $wheres = [];
   protected array $bindings = [];
   protected array $orders = [];
   protected ?int $limit = null;
   protected ?int $offset = null;
   protected array $groups = [];
   protected array $havings = [];

   public function __construct(Connection $connection, string $table)
   {
      $this->connection = $connection;
      $this->table = $table;
   }

   public function select(array|string $columns): self
   {
      $this->selects = is_array($columns) ? $columns : func_get_args();
      return $this;
   }

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

   public function leftJoin(string $table, string $first, string $operator, string $second): self
   {
      return $this->join($table, $first, $operator, $second, 'left');
   }

   public function rightJoin(string $table, string $first, string $operator, string $second): self
   {
      return $this->join($table, $first, $operator, $second, 'right');
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

   public function get(): array
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public function first(): ?array
   {
      $this->limit(1);
      $results = $this->get();
      return $results[0] ?? null;
   }

   public function count(): int
   {
      $sql = $this->toSql();
      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return (int) $stmt->fetchColumn();
   }

   public function exists(): bool
   {
      return $this->count() > 0;
   }

   public function doesntExist(): bool
   {
      return !$this->exists();
   }

   public function insert(array $values): bool
   {
      $columns = array_keys($values);
      $placeholders = array_fill(0, count($values), '?');
      $sql = sprintf(
         'INSERT INTO %s (%s) VALUES (%s)',
         $this->table,
         implode(', ', $columns),
         implode(', ', $placeholders)
      );

      $stmt = $this->connection->prepare($sql);
      return $stmt->execute(array_values($values));
   }

   public function update(array $values): int
   {
      $set = [];
      foreach ($values as $column => $value) {
         $set[] = "{$column} = ?";
         $this->bindings[] = $value;
      }

      $sql = sprintf(
         'UPDATE %s SET %s %s',
         $this->table,
         implode(', ', $set),
         $this->buildWhereClause()
      );

      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return $stmt->rowCount();
   }

   public function delete(): int
   {
      $sql = sprintf(
         'DELETE FROM %s %s',
         $this->table,
         $this->buildWhereClause()
      );

      $stmt = $this->connection->prepare($sql);
      $stmt->execute($this->bindings);
      return $stmt->rowCount();
   }

   protected function toSql(): string
   {
      $sql = ['SELECT ' . implode(', ', $this->selects) . ' FROM ' . $this->table];

      // Joins
      foreach ($this->joins as $join) {
         $sql[] = sprintf(
            '%s JOIN %s ON %s %s %s',
            strtoupper($join['type']),
            $join['table'],
            $join['first'],
            $join['operator'],
            $join['second']
         );
      }

      // Where clauses
      if (!empty($this->wheres)) {
         $sql[] = $this->buildWhereClause();
      }

      // Group by
      if (!empty($this->groups)) {
         $sql[] = 'GROUP BY ' . implode(', ', $this->groups);
      }

      // Having
      if (!empty($this->havings)) {
         $sql[] = $this->buildHavingClause();
      }

      // Order by
      if (!empty($this->orders)) {
         $sql[] = $this->buildOrderByClause();
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
      if (empty($this->wheres)) {
         return '';
      }

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

      return 'WHERE ' . implode(' ', $clauses);
   }

   protected function buildHavingClause(): string
   {
      $clauses = [];
      foreach ($this->havings as $having) {
         $clauses[] = $having['column'] . ' ' . $having['operator'] . ' ?';
         $this->bindings[] = $having['value'];
      }
      return 'HAVING ' . implode(' AND ', $clauses);
   }

   protected function buildOrderByClause(): string
   {
      $clauses = [];
      foreach ($this->orders as $order) {
         $clauses[] = $order['column'] . ' ' . $order['direction'];
      }
      return 'ORDER BY ' . implode(', ', $clauses);
   }
}
