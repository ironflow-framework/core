<?php

namespace IronFlow\Core\Database\Schema;

use InvalidArgumentException;
use RuntimeException;

/**
 * Schema builder class for creating database table structures
 * Provides a fluent interface for defining table columns and constraints
 */
class Anvil
{
    protected string $table;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $foreignKeys = [];
    protected string $engine = 'InnoDB';
    protected string $charset = 'utf8mb4';
    protected string $collation = 'utf8mb4_unicode_ci';

    public function __construct(string $table)
    {
        if (empty($table)) {
            throw new InvalidArgumentException('Table name cannot be empty');
        }
        $this->table = $this->sanitizeIdentifier($table);
    }

    /**
     * Add an auto-incrementing primary key column
     */
    public function id(string $name = 'id'): static
    {
        $name = $this->sanitizeIdentifier($name);
        $this->columns[] = "`{$name}` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    /**
     * Add a string/varchar column
     */
    public function string(string $name, int $length = 255): static
    {
        if ($length <= 0 || $length > 65535) {
            throw new InvalidArgumentException('String length must be between 1 and 65535');
        }
        
        $name = $this->sanitizeIdentifier($name);
        $this->columns[] = "`{$name}` VARCHAR({$length}) NOT NULL";
        return $this;
    }

    /**
     * Add a text column
     */
    public function text(string $name, string $type = 'TEXT'): static
    {
        $allowedTypes = ['TINYTEXT', 'TEXT', 'MEDIUMTEXT', 'LONGTEXT'];
        if (!in_array(strtoupper($type), $allowedTypes)) {
            throw new InvalidArgumentException('Invalid text type. Allowed: ' . implode(', ', $allowedTypes));
        }
        
        $name = $this->sanitizeIdentifier($name);
        $this->columns[] = "`{$name}` {$type} NOT NULL";
        return $this;
    }

    /**
     * Add a boolean column
     */
    public function boolean(string $name): static
    {
        $name = $this->sanitizeIdentifier($name);
        $this->columns[] = "`{$name}` BOOLEAN NOT NULL DEFAULT FALSE";
        return $this;
    }

    /**
     * Add an integer column
     */
    public function integer(string $name, bool $unsigned = false): static
    {
        $name = $this->sanitizeIdentifier($name);
        $unsigned = $unsigned ? ' UNSIGNED' : '';
        $this->columns[] = "`{$name}` INT{$unsigned} NOT NULL";
        return $this;
    }

    /**
     * Add a big integer column
     */
    public function bigInteger(string $name, bool $unsigned = false): static
    {
        $name = $this->sanitizeIdentifier($name);
        $unsigned = $unsigned ? ' UNSIGNED' : '';
        $this->columns[] = "`{$name}` BIGINT{$unsigned} NOT NULL";
        return $this;
    }

    /**
     * Add a decimal column
     */
    public function decimal(string $name, int $precision = 8, int $scale = 2): static
    {
        if ($precision <= 0 || $scale < 0 || $scale > $precision) {
            throw new InvalidArgumentException('Invalid decimal precision or scale');
        }
        
        $name = $this->sanitizeIdentifier($name);
        $this->columns[] = "`{$name}` DECIMAL({$precision},{$scale}) NOT NULL";
        return $this;
    }

    /**
     * Add a JSON column
     */
    public function json(string $name): static
    {
        $name = $this->sanitizeIdentifier($name);
        $this->columns[] = "`{$name}` JSON NOT NULL";
        return $this;
    }

    /**
     * Add an enum column
     */
    public function enum(string $name, array $values): static
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Enum values cannot be empty');
        }
        
        $name = $this->sanitizeIdentifier($name);
        $escaped = array_map(fn($v) => "'" . $this->escapeString((string)$v) . "'", $values);
        $this->columns[] = "`{$name}` ENUM(" . implode(', ', $escaped) . ") NOT NULL";
        return $this;
    }

    /**
     * Add a timestamp column
     */
    public function timestamp(string $name, bool $nullable = false, bool $updateOnChange = false): static
    {
        $name = $this->sanitizeIdentifier($name);
        $line = "`{$name}` TIMESTAMP";
        
        if ($nullable) {
            $line .= " NULL DEFAULT NULL";
        } else {
            $line .= " NOT NULL DEFAULT CURRENT_TIMESTAMP";
        }
        
        if ($updateOnChange) {
            $line .= " ON UPDATE CURRENT_TIMESTAMP";
        }
        
        $this->columns[] = $line;
        return $this;
    }

    /**
     * Add datetime column
     */
    public function dateTime(string $name, bool $nullable = false): static
    {
        $name = $this->sanitizeIdentifier($name);
        $line = "`{$name}` DATETIME";
        $line .= $nullable ? " NULL" : " NOT NULL";
        $this->columns[] = $line;
        return $this;
    }

    /**
     * Add date column
     */
    public function date(string $name, bool $nullable = false): static
    {
        $name = $this->sanitizeIdentifier($name);
        $line = "`{$name}` DATE";
        $line .= $nullable ? " NULL" : " NOT NULL";
        $this->columns[] = $line;
        return $this;
    }

    /**
     * Add created_at and updated_at timestamps
     */
    public function timestamps(): static
    {
        return $this->timestamp('created_at')
                   ->timestamp('updated_at', updateOnChange: true);
    }

    /**
     * Add created_at timestamp
     */
    public function createdAt(): static
    {
        return $this->timestamp('created_at');
    }

    /**
     * Add updated_at timestamp with auto-update
     */
    public function updatedAt(): static
    {
        return $this->timestamp('updated_at', updateOnChange: true);
    }

    /**
     * Add soft delete timestamp
     */
    public function softDeletes(string $column = 'deleted_at'): static
    {
        return $this->timestamp($column, nullable: true);
    }

    /**
     * Make the last column nullable
     */
    public function nullable(): static
    {
        if (empty($this->columns)) {
            throw new RuntimeException('No column to make nullable');
        }
        
        $last = array_pop($this->columns);
        $last = str_replace(' NOT NULL', '', $last);
        $this->columns[] = $last . " NULL";
        return $this;
    }

    /**
     * Set default value for the last column
     */
    public function default(mixed $value): static
    {
        if (empty($this->columns)) {
            throw new RuntimeException('No column to set default value');
        }
        
        $last = array_pop($this->columns);
        
        if ($value === null) {
            $defaultValue = 'NULL';
        } elseif (is_string($value)) {
            $defaultValue = "'" . $this->escapeString($value) . "'";
        } elseif (is_bool($value)) {
            $defaultValue = $value ? 'TRUE' : 'FALSE';
        } else {
            $defaultValue = (string)$value;
        }
        
        $this->columns[] = $last . " DEFAULT {$defaultValue}";
        return $this;
    }

    /**
     * Make the last column unique
     */
    public function unique(): static
    {
        if (empty($this->columns)) {
            throw new RuntimeException('No column to make unique');
        }
        
        $last = array_pop($this->columns);
        $this->columns[] = $last . " UNIQUE";
        return $this;
    }

    /**
     * Add an index on specified columns
     */
    public function index(string|array $columns, ?string $name = null): static
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $columns = array_map([$this, 'sanitizeIdentifier'], $columns);
        $columnList = '`' . implode('`, `', $columns) . '`';
        
        $indexName = $name ?? 'idx_' . $this->table . '_' . implode('_', $columns);
        $indexName = $this->sanitizeIdentifier($indexName);
        
        $this->indexes[] = "INDEX `{$indexName}` ({$columnList})";
        return $this;
    }

    /**
     * Add a foreign key constraint
     */
    public function foreign(string $column, string $refTable, string $refColumn = 'id', string $onDelete = 'CASCADE', string $onUpdate = 'CASCADE'): static
    {
        $column = $this->sanitizeIdentifier($column);
        $refTable = $this->sanitizeIdentifier($refTable);
        $refColumn = $this->sanitizeIdentifier($refColumn);
        
        $constraintName = "fk_{$this->table}_{$column}";
        $constraintName = $this->sanitizeIdentifier($constraintName);
        
        $this->foreignKeys[] = "CONSTRAINT `{$constraintName}` FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}`(`{$refColumn}`) ON DELETE {$onDelete} ON UPDATE {$onUpdate}";
        return $this;
    }

    /**
     * Set the storage engine
     */
    public function engine(string $engine): static
    {
        $allowedEngines = ['InnoDB', 'MyISAM', 'Memory', 'Archive'];
        if (!in_array($engine, $allowedEngines)) {
            throw new InvalidArgumentException('Invalid engine. Allowed: ' . implode(', ', $allowedEngines));
        }
        
        $this->engine = $engine;
        return $this;
    }

    /**
     * Set the character set
     */
    public function charset(string $charset): static
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Set the collation
     */
    public function collation(string $collation): static
    {
        $this->collation = $collation;
        return $this;
    }

    /**
     * Generate the SQL for table creation
     */
    public function toSql(): string
    {
        if (empty($this->columns)) {
            throw new RuntimeException('No columns defined for table');
        }
        
        $elements = array_merge($this->columns, $this->indexes, $this->foreignKeys);
        
        return "CREATE TABLE IF NOT EXISTS `{$this->table}` (\n    " . 
               implode(",\n    ", $elements) . 
               "\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation}";
    }

    /**
     * Sanitize database identifier
     */
    protected function sanitizeIdentifier(string $identifier): string
    {
        // Remove backticks and validate identifier
        $identifier = trim($identifier, '`');
        
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $identifier)) {
            throw new InvalidArgumentException("Invalid identifier: {$identifier}");
        }
        
        return $identifier;
    }

    /**
     * Escape string values
     */
    protected function escapeString(string $value): string
    {
        return str_replace(['\\', "'"], ['\\\\', "''"], $value);
    }
}

