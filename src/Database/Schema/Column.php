<?php

declare(strict_types=1);

namespace IronFlow\Database\Schema;

/**
 * Classe représentant une colonne dans un schéma de base de données
 */
class Column
{
    /**
     * Type de la colonne
     */
    protected string $type;

    /**
     * Nom de la colonne
     */
    protected string $name;

    /**
     * Paramètres de la colonne
     */
    protected array $parameters;

    /**
     * Indique si la colonne est nullable
     */
    protected bool $nullable = false;

    /**
     * Valeur par défaut de la colonne
     */
    protected mixed $default = null;

    /**
     * Indique si la colonne a une valeur par défaut
     */
    protected bool $hasDefault = false;

    /**
     * Indique si la colonne est unique
     */
    protected bool $unique = false;

    /**
     * Indique si la colonne est unsigned
     */
    protected bool $unsigned = false;

    /**
     * Commentaire sur la colonne
     */
    protected ?string $comment = null;

    /**
     * Référence de clé étrangère
     */
    protected ?array $constrained = null;

    /**
     * Action à effectuer lors de la suppression
     */
    protected ?string $onDelete = null;

    /**
     * Action à effectuer lors de la mise à jour
     */
    protected ?string $onUpdate = null;

    /**
     * Position de la colonne (FIRST, AFTER column_name)
     */
    protected ?string $position = null;

    /**
     * Charset de la colonne
     */
    protected ?string $charset = null;

    /**
     * Collation de la colonne
     */
    protected ?string $collation = null;

    /**
     * Indique si la colonne est générée
     */
    protected bool $generated = false;

    /**
     * Expression de génération
     */
    protected ?string $generatedExpression = null;

    /**
     * Type de génération (VIRTUAL ou STORED)
     */
    protected string $generationType = 'VIRTUAL';

    /**
     * Indique si la colonne est virtuelle
     */
    protected bool $virtual = false;

    /**
     * Indique si la colonne est stockée
     */
    protected bool $stored = false;

    /**
     * Constructeur
     */
    public function __construct(string $type, string $name, array $parameters = [])
    {
        $this->type = $type;
        $this->name = $name;
        $this->parameters = $parameters;
    }

    /**
     * Définit la colonne comme nullable
     */
    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;
        return $this;
    }

    /**
     * Définit la valeur par défaut de la colonne
     */
    public function default(mixed $value): self
    {
        $this->default = $value;
        $this->hasDefault = true;
        return $this;
    }

    /**
     * Définit la colonne comme unique
     */
    public function unique(bool $unique = true): self
    {
        $this->unique = $unique;
        return $this;
    }

    /**
     * Définit la colonne comme unsigned
     */
    public function unsigned(bool $unsigned = true): self
    {
        $this->unsigned = $unsigned;
        return $this;
    }

    /**
     * Ajoute un commentaire à la colonne
     */
    public function comment(string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * Définit la table référencée par la colonne
     */
    public function constrained(string $table, ?string $columnRef = null): self
    {
        if ($columnRef) {
            $reference = [$table, $columnRef];
        } else {
            $reference = [$table, 'id'];
        }

        $this->constrained = $reference;
        return $this;
    }

    /**
     * Définit l'action à effectuer lors de la suppression
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = $this->validateAction($action);
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

    /**
     * Définit l'action à effectuer lors de la mise à jour
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = strtoupper($action);
        return $this;
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

    /**
     * Définit la position de la colonne après une autre colonne
     */
    public function after(string $column): self
    {
        $this->position = "AFTER {$column}";
        return $this;
    }

    /**
     * Définit la colonne comme première
     */
    public function first(): self
    {
        $this->position = "FIRST";
        return $this;
    }

    /**
     * Définit le charset de la colonne
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * Définit la collation de la colonne
     */
    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    /**
     * Définit la colonne comme générée
     */
    public function storedAs(string $expression): self
    {
        $this->generated = true;
        $this->generatedExpression = $expression;
        $this->generationType = 'STORED';
        $this->stored = true;
        return $this;
    }

    /**
     * Définit la colonne comme virtuelle générée
     */
    public function virtualAs(string $expression): self
    {
        $this->generated = true;
        $this->generatedExpression = $expression;
        $this->generationType = 'VIRTUAL';
        $this->virtual = true;
        return $this;
    }

    /**
     * Définit une colonne comme auto-incrémentale
     */
    public function autoIncrement(bool $autoIncrement = true): self
    {
        $this->parameters['auto_increment'] = $autoIncrement;
        return $this;
    }

    /**
     * Définit la longueur d'une colonne
     */
    public function length(int $length): self
    {
        $this->parameters['length'] = $length;
        return $this;
    }

    /**
     * Définit la précision et l'échelle pour les nombres décimaux
     */
    public function precision(int $precision, int $scale = 0): self
    {
        $this->parameters['precision'] = $precision;
        $this->parameters['scale'] = $scale;
        return $this;
    }

    /**
     * Renomme la colonne
     */
    public function rename(string $newName): self
    {
        $this->name = $newName;
        return $this;
    }

    /**
     * Change le type de la colonne
     */
    public function changeType(string $newType, array $parameters = []): self
    {
        $this->type = $newType;
        $this->parameters = array_merge($this->parameters, $parameters);
        return $this;
    }

    /**
     * Convertit la colonne en définition SQL
     */
    public function toSql(string $driver): string
    {
        $sql = $this->name . ' ' . $this->getSqlType($driver);

        // Ajouter la valeur par défaut
        if ($this->hasDefault && !$this->generated) {
            $defaultValue = $this->formatDefaultValue($this->default, $driver);
            $sql .= " DEFAULT $defaultValue";
        }

        // Ajouter UNIQUE si applicable
        if ($this->unique) {
            $sql .= " UNIQUE";
        }

        if ($this->unsigned && $this->isNumericType()) {
            $sql .= ' UNSIGNED';
        }

        // Ajouter CHARACTER SET et COLLATE pour les types texte
        if ($this->isTextType() && $driver === 'mysql') {
            if ($this->charset) {
                $sql .= " CHARACTER SET {$this->charset}";
            }
            if ($this->collation) {
                $sql .= " COLLATE {$this->collation}";
            }
        }

        // Ajouter NOT NULL ou NULL
        if (!$this->nullable && !$this->generated) {
            $sql .= ' NOT NULL';
        } elseif ($this->nullable) {
            $sql .= ' NULL';
        }

        // Ajouter AUTO_INCREMENT si applicable
        if (isset($this->parameters['auto_increment']) && $this->parameters['auto_increment']) {
            switch ($driver) {
                case 'mysql':
                    $sql .= ' AUTO_INCREMENT';
                    break;
                case 'sqlite':
                    // SQLite gère automatiquement l'auto-increment pour INTEGER PRIMARY KEY
                    break;
                case 'pgsql':
                    // PostgreSQL utilise SERIAL, déjà géré dans getSqlType()
                    break;
            }
        }

        // Ajouter la génération si applicable
        if ($this->generated) {
            $sql .= " GENERATED ALWAYS AS ({$this->generatedExpression}) {$this->generationType}";
        }

        // Ajouter un commentaire si applicable
        if ($this->comment !== null && $driver === 'mysql') {
            $sql .= " COMMENT '" . addslashes($this->comment) . "'";
        }

        // Ajouter la contrainte de clé étrangère inline si définie
        if ($this->constrained !== null) {
            $sql .= " REFERENCES {$this->constrained[0]}({$this->constrained[1]})";

            if ($this->onDelete) {
                $sql .= " ON DELETE {$this->onDelete}";
            }

            if ($this->onUpdate) {
                $sql .= " ON UPDATE {$this->onUpdate}";
            }
        }

        // Ajouter la position si définie (MySQL)
        if ($this->position !== null && $driver === 'mysql') {
            $sql .= " {$this->position}";
        }

        return $sql;
    }

    /**
     * Obtient le type SQL en fonction du driver de base de données
     */
    protected function getSqlType(string $driver): string
    {
        switch ($this->type) {
            case 'integer':
                return match ($driver) {
                    'mysql' => 'INT',
                    'sqlite' => 'INTEGER',
                    'pgsql' => isset($this->parameters['auto_increment']) && $this->parameters['auto_increment'] ? 'SERIAL' : 'INTEGER',
                    default => 'INTEGER'
                };

            case 'bigint':
                return match ($driver) {
                    'mysql' => 'BIGINT',
                    'sqlite' => 'INTEGER',
                    'pgsql' => isset($this->parameters['auto_increment']) && $this->parameters['auto_increment'] ? 'BIGSERIAL' : 'BIGINT',
                    default => 'BIGINT'
                };

            case 'smallint':
                return match ($driver) {
                    'mysql' => 'SMALLINT',
                    'sqlite' => 'INTEGER',
                    'pgsql' => isset($this->parameters['auto_increment']) && $this->parameters['auto_increment'] ? 'SMALLSERIAL' : 'SMALLINT',
                    default => 'SMALLINT'
                };

            case 'tinyint':
                return match ($driver) {
                    'mysql' => 'TINYINT',
                    'sqlite' => 'INTEGER',
                    'pgsql' => 'SMALLINT',
                    default => 'TINYINT'
                };

            case 'mediumint':
                return match ($driver) {
                    'mysql' => 'MEDIUMINT',
                    'sqlite' => 'INTEGER',
                    'pgsql' => 'INTEGER',
                    default => 'MEDIUMINT'
                };

            case 'varchar':
                $length = $this->parameters['length'] ?? 255;
                return match ($driver) {
                    'mysql', 'pgsql' => "VARCHAR($length)",
                    'sqlite' => 'TEXT',
                    default => "VARCHAR($length)"
                };

            case 'char':
                $length = $this->parameters['length'] ?? 1;
                return match ($driver) {
                    'mysql', 'pgsql' => "CHAR($length)",
                    'sqlite' => 'TEXT',
                    default => "CHAR($length)"
                };

            case 'text':
                return 'TEXT';

            case 'mediumtext':
                return match ($driver) {
                    'mysql' => 'MEDIUMTEXT',
                    'sqlite', 'pgsql' => 'TEXT',
                    default => 'TEXT'
                };

            case 'longtext':
                return match ($driver) {
                    'mysql' => 'LONGTEXT',
                    'sqlite', 'pgsql' => 'TEXT',
                    default => 'TEXT'
                };

            case 'boolean':
                return match ($driver) {
                    'mysql' => 'TINYINT(1)',
                    'sqlite' => 'INTEGER',
                    'pgsql' => 'BOOLEAN',
                    default => 'BOOLEAN'
                };

            case 'date':
                return 'DATE';

            case 'datetime':
                return match ($driver) {
                    'mysql' => 'DATETIME',
                    'sqlite' => 'DATETIME',
                    'pgsql' => 'TIMESTAMP',
                    default => 'DATETIME'
                };

            case 'time':
                return 'TIME';

            case 'timestamp':
                return match ($driver) {
                    'mysql' => 'TIMESTAMP',
                    'sqlite' => 'DATETIME',
                    'pgsql' => 'TIMESTAMP',
                    default => 'TIMESTAMP'
                };

            case 'decimal':
                $precision = $this->parameters['precision'] ?? 8;
                $scale = $this->parameters['scale'] ?? 2;
                return "DECIMAL($precision, $scale)";

            case 'float':
                return match ($driver) {
                    'mysql' => 'FLOAT',
                    'sqlite' => 'REAL',
                    'pgsql' => 'REAL',
                    default => 'FLOAT'
                };

            case 'double':
                return match ($driver) {
                    'mysql' => 'DOUBLE',
                    'sqlite' => 'REAL',
                    'pgsql' => 'DOUBLE PRECISION',
                    default => 'DOUBLE'
                };

            case 'real':
                return match ($driver) {
                    'mysql' => 'DOUBLE',
                    'sqlite' => 'REAL',
                    'pgsql' => 'REAL',
                    default => 'REAL'
                };

            case 'enum':
                $values = $this->parameters['values'] ?? [];
                $enumValues = "'" . implode("','", $values) . "'";
                return match ($driver) {
                    'mysql' => "ENUM($enumValues)",
                    'sqlite' => 'TEXT',
                    'pgsql' => "VARCHAR(255) CHECK ({$this->name} IN ($enumValues))",
                    default => "ENUM($enumValues)"
                };

            case 'set':
                $values = $this->parameters['values'] ?? [];
                $setValues = "'" . implode("','", $values) . "'";
                return match ($driver) {
                    'mysql' => "SET($setValues)",
                    'sqlite', 'pgsql' => 'TEXT',
                    default => "SET($setValues)"
                };

            case 'blob':
                return match ($driver) {
                    'mysql' => 'BLOB',
                    'sqlite' => 'BLOB',
                    'pgsql' => 'BYTEA',
                    default => 'BLOB'
                };

            case 'json':
                return match ($driver) {
                    'mysql' => 'JSON',
                    'sqlite' => 'TEXT',
                    'pgsql' => 'JSON',
                    default => 'JSON'
                };

            case 'uuid':
                return match ($driver) {
                    'mysql' => 'CHAR(36)',
                    'sqlite' => 'TEXT',
                    'pgsql' => 'UUID',
                    default => 'CHAR(36)'
                };

            case 'ipaddress':
                return match ($driver) {
                    'mysql' => 'VARCHAR(45)',
                    'sqlite' => 'TEXT',
                    'pgsql' => 'INET',
                    default => 'VARCHAR(45)'
                };

            case 'macaddress':
                return match ($driver) {
                    'mysql' => 'VARCHAR(17)',
                    'sqlite' => 'TEXT',
                    'pgsql' => 'MACADDR',
                    default => 'VARCHAR(17)'
                };

            default:
                return $this->type;
        }
    }

    /**
     * Formate la valeur par défaut pour SQL
     */
    protected function formatDefaultValue(mixed $value, string $driver): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return match ($driver) {
                'mysql' => $value ? '1' : '0',
                'pgsql' => $value ? 'TRUE' : 'FALSE',
                default => $value ? '1' : '0'
            };
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        if (is_string($value)) {
            // Gérer les fonctions SQL spéciales
            $sqlFunctions = [
                'CURRENT_TIMESTAMP',
                'NOW()',
                'CURRENT_DATE',
                'CURRENT_TIME',
                'UUID()',
                'RAND()',
                'RANDOM()',
                'UNIX_TIMESTAMP()'
            ];

            if (in_array(strtoupper($value), $sqlFunctions)) {
                return $value;
            }

            return "'" . addslashes($value) . "'";
        }

        if (is_array($value)) {
            return "'" . addslashes(json_encode($value)) . "'";
        }

        return "'" . addslashes((string) $value) . "'";
    }

    /**
     * Vérifie si le type est numérique
     */
    protected function isNumericType(): bool
    {
        return in_array($this->type, [
            'integer',
            'bigint',
            'smallint',
            'tinyint',
            'mediumint',
            'decimal',
            'float',
            'double',
            'real'
        ]);
    }

    /**
     * Vérifie si le type est textuel
     */
    protected function isTextType(): bool
    {
        return in_array($this->type, [
            'varchar',
            'char',
            'text',
            'mediumtext',
            'longtext'
        ]);
    }

    /**
     * Vérifie si le type est temporel
     */
    protected function isDateTimeType(): bool
    {
        return in_array($this->type, [
            'date',
            'datetime',
            'time',
            'timestamp'
        ]);
    }

    /**
     * Obtient le nom de la colonne
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Obtient le type de la colonne
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Obtient les paramètres de la colonne
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Vérifie si la colonne est nullable
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Vérifie si la colonne a une valeur par défaut
     */
    public function hasDefaultValue(): bool
    {
        return $this->hasDefault;
    }

    /**
     * Obtient la valeur par défaut
     */
    public function getDefaultValue(): mixed
    {
        return $this->default;
    }

    /**
     * Vérifie si la colonne est unique
     */
    public function isUnique(): bool
    {
        return $this->unique;
    }

    /**
     * Vérifie si la colonne est unsigned
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * Obtient le commentaire de la colonne
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Vérifie si la colonne est générée
     */
    public function isGenerated(): bool
    {
        return $this->generated;
    }

    /**
     * Vérifie si la colonne est auto-incrémentale
     */
    public function isAutoIncrement(): bool
    {
        return $this->parameters['auto_increment'] ?? false;
    }

    /**
     * Clone la colonne
     */
    public function __clone()
    {
        // Assure que les paramètres sont également clonés
        array_replace($this->parameters, $this->parameters);
    }
}
