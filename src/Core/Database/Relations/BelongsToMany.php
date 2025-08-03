<?php 

declare(strict_types=1);

namespace IronFlow\Core\Database\Relations;

use IronFlow\Core\Database\Collection;
use IronFlow\Core\Database\Model;

/**
 * Relation BelongsToMany (Plusieurs à Plusieurs)
 */
class BelongsToMany extends Relation
{
    protected string $table;
    protected string $relatedPivotKey;
    protected string $parentPivotKey;

    public function __construct(
        Model $parent, 
        string $related, 
        ?string $table = null, 
        ?string $foreignPivotKey = null, 
        ?string $relatedPivotKey = null,
        ?string $parentKey = null,
        ?string $relatedKey = null
    ) {
        $this->table = $table ?? $this->getTable($parent, $related);
        $this->parentPivotKey = $foreignPivotKey ?? $this->getForeignKey();
        $this->relatedPivotKey = $relatedPivotKey ?? $this->getRelatedForeignKey($related);
        
        parent::__construct($parent, $related, $parentKey, $relatedKey);
    }

    /**
     * Obtient le nom de la table pivot
     */
    protected function getTable(Model $parent, string $related): string
    {
        $parentTable = $parent::getTable();
        $relatedTable = (new $related())->getTable();
        
        $tables = [$parentTable, $relatedTable];
        sort($tables);
        
        return implode('_', $tables);
    }

    /**
     * Obtient la clé étrangère du modèle lié
     */
    protected function getRelatedForeignKey(string $related): string
    {
        $relatedClass = (new \ReflectionClass($related))->getShortName();
        return strtolower($relatedClass) . '_id';
    }

    public function getQuery()
    {
        $related = $this->newRelatedInstance();
        return $related::query()
            ->join($this->table, $related::getTable() . '.id', '=', $this->table . '.' . $this->relatedPivotKey)
            ->where($this->table . '.' . $this->parentPivotKey, $this->parent->getKey())
            ->select($related::getTable() . '.*');
    }

    public function getResults(): Collection
    {
        $results = $this->getQuery()->get();
        if ($results instanceof Collection) {
            return $results->map(fn($item) => is_array($item) ? $this->related::newFromBuilder($item) : $item);
        }
        return collect($results)->map(fn($item) => is_array($item) ? $this->related::newFromBuilder($item) : $item);
    }

    /**
     * Attache des modèles liés
     */
    public function attach(mixed $id, array $attributes = []): void
    {
        $this->parent::query()
            ->from($this->table)
            ->insert(array_merge([
                $this->parentPivotKey => $this->parent->getKey(),
                $this->relatedPivotKey => $id
            ], $attributes));
    }

    /**
     * Détache des modèles liés
     */
    public function detach(mixed $ids = null): int
    {
        $query = $this->parent::query()
            ->from($this->table)
            ->where($this->parentPivotKey, $this->parent->getKey());

        if ($ids !== null) {
            $ids = is_array($ids) ? $ids : [$ids];
            $query->whereIn($this->relatedPivotKey, $ids);
        }

        return $query->delete();
    }

    /**
     * Synchronise les relations
     */
    public function sync(array $ids): array
    {
        $current = $this->getResults()->pluck('id')->toArray();
        
        $toDetach = array_diff($current, $ids);
        $toAttach = array_diff($ids, $current);
        
        if (!empty($toDetach)) {
            $this->detach($toDetach);
        }
        
        foreach ($toAttach as $id) {
            $this->attach($id);
        }
        
        return [
            'attached' => $toAttach,
            'detached' => $toDetach,
            'updated' => []
        ];
    }
}