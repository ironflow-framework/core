<?php

declare(strict_types=1);

namespace IronFlow\Core\Database;

use PDO;

/**
 * Query Builder - Constructeur de requêtes SQL fluide
 * Version améliorée avec plus de fonctionnalités
 */
class QueryBuilder
{
    private PDO $pdo;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private array $selects = ['*'];
    private array $joins = [];
    private array $orders = [];
    private ?int $limitCount = null;
    private ?int $offsetCount = null;
    private array $groups = [];
    private array $havings = [];
    private array $unions = [];

    public function __construct(PDO $pdo, string $table)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    /**
     * Change la table cible
     */
    public function from(string $table): static 
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Récupère le nom de la table
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Sélectionne les colonnes
     */
    public function select(string|array $columns = ['*']): self
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Ajoute des colonnes à la sélection
     */
    public function addSelect(string|array $columns): self
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $this->selects = array_merge($this->selects, $columns);
        return $this;
    }

    /**
     * Sélection distincte
     */
    public function distinct(): self
    {
        $this->selects = array_unique(array_merge(['DISTINCT'], $this->selects));
        return $this;
    }

    /**
     * Clause WHERE simple
     */
    public function where(string $column, string $operator = '=', mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic', 
            'column' => $column, 
            'operator' => $operator, 
            'value' => $value, 
            'boolean' => 'and'
        ];
        $this->bindings[] = $value;
        
        return $this;
    }

    /**
     * Clause WHERE avec OR
     */
    public function orWhere(string $column, string $operator = '=', mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic', 
            'column' => $column, 
            'operator' => $operator, 
            'value' => $value, 
            'boolean' => 'or'
        ];
        $this->bindings[] = $value;
        
        return $this;
    }

    /**
     * WHERE avec plusieurs conditions (array)
     */
    public function whereArray(array $conditions): self
    {
        foreach ($conditions as $condition) {
            if (is_array($condition)) {
                $column = $condition[0];
                $operator = $condition[1] ?? '=';
                $value = $condition[2] ?? null;
                $this->where($column, $operator, $value);
            }
        }
        return $this;
    }

    /**
     * WHERE IN clause
     */
    public function whereIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this->whereRaw('1 = 0'); // Condition impossible
        }

        $this->wheres[] = [
            'type' => 'in', 
            'column' => $column, 
            'values' => $values, 
            'boolean' => 'and'
        ];
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }

    /**
     * WHERE NOT IN clause
     */
    public function whereNotIn(string $column, array $values): self
    {
        if (empty($values)) {
            return $this; // Pas de condition
        }

        $this->wheres[] = [
            'type' => 'not_in', 
            'column' => $column, 
            'values' => $values, 
            'boolean' => 'and'
        ];
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }

    /**
     * WHERE BETWEEN clause
     */
    public function whereBetween(string $column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'between', 
            'column' => $column, 
            'values' => $values, 
            'boolean' => 'and'
        ];
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }

    /**
     * WHERE NOT BETWEEN clause
     */
    public function whereNotBetween(string $column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'not_between', 
            'column' => $column, 
            'values' => $values, 
            'boolean' => 'and'
        ];
        $this->bindings = array_merge($this->bindings, $values);
        
        return $this;
    }

    /**
     * WHERE NULL clause
     */
    public function whereNull(string $column): self
    {
        $this->wheres[] = [
            'type' => 'null', 
            'column' => $column, 
            'boolean' => 'and'
        ];
        
        return $this;
    }

    /**
     * WHERE NOT NULL clause
     */
    public function whereNotNull(string $column): self
    {
        $this->wheres[] = [
            'type' => 'not_null', 
            'column' => $column, 
            'boolean' => 'and'
        ];
        
        return $this;
    }

    /**
     * WHERE LIKE clause
     */
    public function whereLike(string $column, string $value): self
    {
        return $this->where($column, 'LIKE', $value);
    }

    /**
     * WHERE raw SQL
     */
    public function whereRaw(string $sql, array $bindings = []): self
    {
        $this->wheres[] = [
            'type' => 'raw', 
            'sql' => $sql, 
            'boolean' => 'and'
        ];
        $this->bindings = array_merge($this->bindings, $bindings);
        
        return $this;
    }

    /**
     * WHERE avec sous-requête
     */
    public function whereExists(\Closure $callback): self
    {
        $query = new self($this->pdo, '');
        $callback($query);
        
        $this->wheres[] = [
            'type' => 'exists', 
            'query' => $query, 
            'boolean' => 'and'
        ];
        $this->bindings = array_merge($this->bindings, $query->getBindings());
        
        return $this;
    }

    /**
     * JOIN clause
     */
    public function join(string $table, string $first, string $operator = '=', ?string $second = null): self
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'inner', 
            'table' => $table, 
            'first' => $first, 
            'operator' => $operator, 
            'second' => $second
        ];
        return $this;
    }

    /**
     * LEFT JOIN clause
     */
    public function leftJoin(string $table, string $first, string $operator = '=', ?string $second = null): self
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'left', 
            'table' => $table, 
            'first' => $first, 
            'operator' => $operator, 
            'second' => $second
        ];
        return $this;
    }

    /**
     * RIGHT JOIN clause
     */
    public function rightJoin(string $table, string $first, string $operator = '=', ?string $second = null): self
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = [
            'type' => 'right', 
            'table' => $table, 
            'first' => $first, 
            'operator' => $operator, 
            'second' => $second
        ];
        return $this;
    }

    /**
     * CROSS JOIN clause
     */
    public function crossJoin(string $table): self
    {
        $this->joins[] = [
            'type' => 'cross', 
            'table' => $table
        ];
        return $this;
    }

    /**
     * ORDER BY clause
     */
    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = ['column' => $column, 'direction' => strtolower($direction)];
        return $this;
    }

    /**
     * ORDER BY aléatoire
     */
    public function inRandomOrder(): self
    {
        return $this->orderBy('RAND()');
    }

    /**
     * ORDER BY avec raw SQL
     */
    public function orderByRaw(string $sql): self
    {
        $this->orders[] = ['raw' => $sql];
        return $this;
    }

    /**
     * GROUP BY clause
     */
    public function groupBy(string|array $columns): self
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $this->groups = array_merge($this->groups, $columns);
        return $this;
    }

    /**
     * HAVING clause
     */
    public function having(string $column, string $operator = '=', mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = ['column' => $column, 'operator' => $operator, 'value' => $value];
        $this->bindings[] = $value;
        
        return $this;
    }

    /**
     * HAVING raw SQL
     */
    public function havingRaw(string $sql, array $bindings = []): self
    {
        $this->havings[] = ['raw' => $sql];
        $this->bindings = array_merge($this->bindings, $bindings);
        
        return $this;
    }

    /**
     * LIMIT clause
     */
    public function limit(int $limit): self
    {
        $this->limitCount = $limit;
        return $this;
    }

    /**
     * TAKE (alias pour LIMIT)
     */
    public function take(int $limit): self
    {
        return $this->limit($limit);
    }

    /**
     * OFFSET clause
     */
    public function offset(int $offset): self
    {
        $this->offsetCount = $offset;
        return $this;
    }

    /**
     * SKIP (alias pour OFFSET)
     */
    public function skip(int $offset): self
    {
        return $this->offset($offset);
    }

    /**
     * UNION clause
     */
    public function union(QueryBuilder $query): self
    {
        $this->unions[] = ['query' => $query, 'all' => false];
        $this->bindings = array_merge($this->bindings, $query->getBindings());
        return $this;
    }

    /**
     * UNION ALL clause
     */
    public function unionAll(QueryBuilder $query): self
    {
        $this->unions[] = ['query' => $query, 'all' => true];
        $this->bindings = array_merge($this->bindings, $query->getBindings());
        return $this;
    }

    /**
     * Exécute la requête et retourne tous les résultats
     */
    public function get(): Collection
    {
        $sql = $this->toSql();
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return new Collection($statement->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Retourne le premier résultat
     */
    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results->first();
    }

    /**
     * Trouve un enregistrement par ID
     */
    public function find(mixed $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Retourne une valeur d'une colonne spécifique
     */
    public function value(string $column): mixed
    {
        $result = $this->select($column)->first();
        return $result ? $result[$column] : null;
    }

    /**
     * Retourne toutes les valeurs d'une colonne
     */
    public function pluck(string $column, ?string $key = null): array
    {
        $results = $this->get()->toArray();
        
        if ($key === null) {
            return array_column($results, $column);
        }
        
        return array_column($results, $column, $key);
    }

    /**
     * Vérifie si des résultats existent
     */
    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * Vérifie si aucun résultat n'existe
     */
    public function doesntExist(): bool
    {
        return !$this->exists();
    }

    /**
     * INSERT
     */
    public function insert(array $data): bool
    {
        // Insertion simple
        if (!is_array(reset($data))) {
            return $this->insertSingle($data);
        }
        
        // Insertion multiple
        return $this->insertMultiple($data);
    }

    /**
     * INSERT simple
     */
    protected function insertSingle(array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $columns) . "`) VALUES ({$placeholders})";
        
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($values);
    }

    /**
     * INSERT multiple
     */
    protected function insertMultiple(array $data): bool
    {
        if (empty($data)) {
            return true;
        }

        $columns = array_keys($data[0]);
        $values = [];
        $bindings = [];
        
        foreach ($data as $record) {
            $recordValues = [];
            foreach ($columns as $column) {
                $recordValues[] = '?';
                $bindings[] = $record[$column] ?? null;
            }
            $values[] = '(' . implode(',', $recordValues) . ')';
        }

        $sql = "INSERT INTO `{$this->table}` (`" . implode('`, `', $columns) . "`) VALUES " . implode(', ', $values);
        
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($bindings);
    }

    /**
     * INSERT OR IGNORE
     */
    public function insertOrIgnore(array $data): bool
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        
        $sql = "INSERT IGNORE INTO `{$this->table}` (`" . implode('`, `', $columns) . "`) VALUES ({$placeholders})";
        
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($values);
    }

    /**
     * UPDATE
     */
    public function update(array $data): int
    {
        $sets = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "`{$column}` = ?";
            $values[] = $value;
        }
        
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
            $values = array_merge($values, $this->bindings);
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        
        return $statement->rowCount();
    }

    /**
     * UPDATE OR INSERT (UPSERT)
     */
    public function updateOrInsert(array $attributes, array $values = []): bool
    {
        // Vérifier si l'enregistrement existe
        $query = clone $this;
        foreach ($attributes as $column => $value) {
            $query->where($column, $value);
        }

        if ($query->exists()) {
            // Mettre à jour
            return $query->update($values) > 0;
        } else {
            // Insérer
            return $this->insert(array_merge($attributes, $values));
        }
    }

    /**
     * INCREMENT une colonne
     */
    public function increment(string $column, int $amount = 1, array $extra = []): int
    {
        $updates = array_merge($extra, [
            $column => $this->pdo->quote($column) . ' + ' . $amount
        ]);

        $sets = [];
        $values = [];
        
        foreach ($updates as $col => $value) {
            if ($col === $column) {
                $sets[] = "`{$col}` = `{$col}` + ?";
                $values[] = $amount;
            } else {
                $sets[] = "`{$col}` = ?";
                $values[] = $value;
            }
        }
        
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets);
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
            $values = array_merge($values, $this->bindings);
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($values);
        
        return $statement->rowCount();
    }

    /**
     * DECREMENT une colonne
     */
    public function decrement(string $column, int $amount = 1, array $extra = []): int
    {
        return $this->increment($column, -$amount, $extra);
    }

    /**
     * DELETE
     */
    public function delete(): int
    {
        $sql = "DELETE FROM `{$this->table}`";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->rowCount();
    }

    /**
     * TRUNCATE
     */
    public function truncate(): bool
    {
        $sql = "TRUNCATE TABLE `{$this->table}`";
        $statement = $this->pdo->prepare($sql);
        return $statement->execute();
    }

    /**
     * COUNT
     */
    public function count(string $column = '*'): int
    {
        $sql = "SELECT COUNT({$column}) as count FROM `{$this->table}`";
        
        if (!empty($this->joins)) {
            foreach ($this->joins as $join) {
                $type = strtoupper($join['type']);
                if ($type === 'CROSS') {
                    $sql .= " CROSS JOIN `{$join['table']}`";
                } else {
                    $sql .= " {$type} JOIN `{$join['table']}` ON `{$join['first']}` {$join['operator']} `{$join['second']}`";
                }
            }
        }
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', array_map(fn($col) => "`{$col}`", $this->groups));
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return (int) $statement->fetchColumn();
    }

    /**
     * MIN
     */
    public function min(string $column): mixed
    {
        $sql = "SELECT MIN(`{$column}`) as min_value FROM `{$this->table}`";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->fetchColumn();
    }

    /**
     * MAX
     */
    public function max(string $column): mixed
    {
        $sql = "SELECT MAX(`{$column}`) as max_value FROM `{$this->table}`";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->fetchColumn();
    }

    /**
     * AVG
     */
    public function avg(string $column): mixed
    {
        $sql = "SELECT AVG(`{$column}`) as avg_value FROM `{$this->table}`";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->fetchColumn();
    }

    /**
     * SUM
     */
    public function sum(string $column): mixed
    {
        $sql = "SELECT SUM(`{$column}`) as sum_value FROM `{$this->table}`";
        
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute($this->bindings);
        
        return $statement->fetchColumn();
    }

    /**
     * Pagination
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Compte total
        $totalQuery = clone $this;
        $total = $totalQuery->count();
        
        // Récupération des données avec pagination
        $this->limit($perPage)->offset($offset);
        $items = $this->get();
        
        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
            'has_more_pages' => $page < ceil($total / $perPage)
        ];
    }

    /**
     * Pagination simple (suivant/précédent)
     */
    public function simplePaginate(int $perPage = 15, int $page = 1): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Récupérer un élément de plus pour savoir s'il y a une page suivante
        $this->limit($perPage + 1)->offset($offset);
        $items = $this->get();
        
        $hasMorePages = $items->count() > $perPage;
        
        // Retirer l'élément supplémentaire
        if ($hasMorePages) {
            $items = $items->take($perPage);
        }
        
        return [
            'data' => $items,
            'current_page' => $page,
            'per_page' => $perPage,
            'has_more_pages' => $hasMorePages,
            'from' => $offset + 1,
            'to' => $offset + $items->count()
        ];
    }

    /**
     * Récupère les bindings
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Construit la clause WHERE
     */
    private function buildWheres(): string
    {
        $wheres = [];
        
        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? '' : strtoupper($where['boolean']) . ' ';
            
            switch ($where['type']) {
                case 'basic':
                    $wheres[] = $boolean . "`{$where['column']}` {$where['operator']} ?";
                    break;
                    
                case 'in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $wheres[] = $boolean . "`{$where['column']}` IN ({$placeholders})";
                    break;
                    
                case 'not_in':
                    $placeholders = str_repeat('?,', count($where['values']) - 1) . '?';
                    $wheres[] = $boolean . "`{$where['column']}` NOT IN ({$placeholders})";
                    break;
                    
                case 'between':
                    $wheres[] = $boolean . "`{$where['column']}` BETWEEN ? AND ?";
                    break;
                    
                case 'not_between':
                    $wheres[] = $boolean . "`{$where['column']}` NOT BETWEEN ? AND ?";
                    break;
                    
                case 'null':
                    $wheres[] = $boolean . "`{$where['column']}` IS NULL";
                    break;
                    
                case 'not_null':
                    $wheres[] = $boolean . "`{$where['column']}` IS NOT NULL";
                    break;
                    
                case 'raw':
                    $wheres[] = $boolean . $where['sql'];
                    break;
                    
                case 'exists':
                    $wheres[] = $boolean . "EXISTS ({$where['query']->toSql()})";
                    break;
            }
        }
        
        return implode(' ', $wheres);
    }

    /**
     * Construit la requête SQL complète
     */
    public function toSql(): string
    {
        $sql = "SELECT " . implode(', ', $this->selects) . " FROM `{$this->table}`";
        
        // Joins
        foreach ($this->joins as $join) {
            $type = strtoupper($join['type']);
            if ($type === 'CROSS') {
                $sql .= " CROSS JOIN `{$join['table']}`";
            } else {
                $sql .= " {$type} JOIN `{$join['table']}` ON `{$join['first']}` {$join['operator']} `{$join['second']}`";
            }
        }
        
        // WHERE
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->buildWheres();
        }
        
        // GROUP BY
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', array_map(fn($col) => "`{$col}`", $this->groups));
        }
        
        // HAVING
        if (!empty($this->havings)) {
            $havings = [];
            foreach ($this->havings as $having) {
                if (isset($having['raw'])) {
                    $havings[] = $having['raw'];
                } else {
                    $havings[] = "`{$having['column']}` {$having['operator']} ?";
                }
            }
            $sql .= ' HAVING ' . implode(' AND ', $havings);
        }
        
        // UNION
        foreach ($this->unions as $union) {
            $unionType = $union['all'] ? 'UNION ALL' : 'UNION';
            $sql .= " {$unionType} ({$union['query']->toSql()})";
        }
        
        // ORDER BY
        if (!empty($this->orders)) {
            $orders = [];
            foreach ($this->orders as $order) {
                if (isset($order['raw'])) {
                    $orders[] = $order['raw'];
                } else {
                    $orders[] = "`{$order['column']}` " . strtoupper($order['direction']);
                }
            }
            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }
        
        // LIMIT
        if ($this->limitCount !== null) {
            $sql .= " LIMIT {$this->limitCount}";
        }
        
        // OFFSET
        if ($this->offsetCount !== null) {
            $sql .= " OFFSET {$this->offsetCount}";
        }
        
        return $sql;
    }

    /**
     * Debug - Affiche la requête SQL avec les bindings
     */
    public function toSqlWithBindings(): string
    {
        $sql = $this->toSql();
        
        foreach ($this->bindings as $binding) {
            $value = is_string($binding) ? "'{$binding}'" : $binding;
            $sql = preg_replace('/\?/', $value, $sql, 1);
        }
        
        return $sql;
    }

    /**
     * Debug - Dump la requête et die
     */
    public function dd(): never
    {
        echo "<pre>";
        echo "SQL: " . $this->toSql() . "\n";
        echo "Bindings: " . json_encode($this->bindings, JSON_THROW_ON_ERROR) . "\n";
        echo "Full SQL: " . $this->toSqlWithBindings() . "\n";
        echo "</pre>";
        die();
    }

    /**
     * Debug - Dump la requête
     */
    public function dump(): self
    {
        echo "<pre>";
        echo "SQL: " . $this->toSql() . "\n";
        echo "Bindings: " . json_encode($this->bindings, JSON_THROW_ON_ERROR) . "\n";
        echo "Full SQL: " . $this->toSqlWithBindings() . "\n";
        echo "</pre>";
        
        return $this;
    }

    /**
     * Clone le query builder
     */
    public function __clone()
    {
        // Pas besoin de faire quoi que ce soit de spécial,
        // PHP clone automatiquement les propriétés
    }

    /**
     * Conversion en string pour debug
     */
    public function __toString(): string
    {
        return $this->toSql();
    }
}