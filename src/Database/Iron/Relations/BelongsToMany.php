<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Relations;

use DateTime;
use IronFlow\Database\Collection;
use IronFlow\Database\Connection;
use IronFlow\Database\Iron\Relations\Query\PivotQueryBuilder;
use IronFlow\Database\Model;

/**
 * Relation BelongsToMany (Plusieurs à Plusieurs)
 * 
 * Cette relation indique que deux modèles sont liés par une relation
 * plusieurs-à-plusieurs via une table pivot.
 */
class BelongsToMany extends Relation
{
   /**
    * Nom de la table pivot
    * 
    * @var string
    */
   protected string $table;

   /**
    * Clé étrangère du modèle parent dans la table pivot
    * 
    * @var string
    */
   protected string $foreignPivotKey;

   /**
    * Clé étrangère du modèle lié dans la table pivot
    * 
    * @var string
    */
   protected string $relatedPivotKey;

   /**
    * Clé primaire du modèle parent
    * 
    * @var string
    */
   protected string $parentKey;

   /**
    * Clé primaire du modèle lié
    * 
    * @var string
    */
   protected string $relatedKey;

   /**
    * Colonnes supplémentaires à sélectionner sur la table pivot
    * 
    * @var array
    */
   protected array $pivotColumns = [];

   /**
    * Valeurs par défaut pour les attributs de la table pivot
    * 
    * @var array
    */
   protected array $pivotDefaults = [];

   /**
    * Indique si les timestamps doivent être gérés sur la table pivot
    * 
    * @var bool
    */
   protected bool $withTimestamps = false;

   /**
    * Constructeur
    * 
    * @param Model $related Modèle cible de la relation
    * @param Model $parent Modèle parent de la relation
    * @param string $table Nom de la table pivot
    * @param string $foreignPivotKey Clé étrangère du modèle parent dans la table pivot
    * @param string $relatedPivotKey Clé étrangère du modèle lié dans la table pivot
    * @param string $parentKey Clé primaire du modèle parent
    * @param string $relatedKey Clé primaire du modèle lié
    */
   public function __construct(
      Model $related,
      Model $parent,
      string $table,
      string $foreignPivotKey,
      string $relatedPivotKey,
      string $parentKey,
      string $relatedKey
   ) {
      $this->related = $related;
      $this->parent = $parent;
      $this->table = $table;
      $this->foreignPivotKey = $foreignPivotKey;
      $this->relatedPivotKey = $relatedPivotKey;
      $this->parentKey = $parentKey;
      $this->relatedKey = $relatedKey;

      $this->initQuery();
   }

   /**
    * Initialise le builder de requête pour la relation
    * 
    * @return void
    */
   protected function initQuery(): void
   {
      $this->query = $this->related::query();

      $this->query->join(
         $this->table,
         $this->related->getTable() . '.' . $this->relatedKey,
         '=',
         $this->table . '.' . $this->relatedPivotKey
      );

      $this->query->where($this->table . '.' . $this->foreignPivotKey, $this->parent->{$this->parentKey});

      $this->query->select($this->related->getTable() . '.*');
   }

   /**
    * Spécifie les colonnes supplémentaires à sélectionner sur la table pivot
    * 
    * @param array $columns Colonnes à sélectionner
    * @return $this
    */
   public function withPivot(array $columns): self
   {
      $this->pivotColumns = array_merge($this->pivotColumns, $columns);

      $pivotColumns = array_map(function ($column) {
         return $this->table . '.' . $column . ' as pivot_' . $column;
      }, $this->pivotColumns);

      $this->query->select([
         $this->related->getTable() . '.*',
         $this->table . '.' . $this->foreignPivotKey . ' as pivot_' . $this->foreignPivotKey,
         $this->table . '.' . $this->relatedPivotKey . ' as pivot_' . $this->relatedPivotKey,
         ...$pivotColumns
      ]);

      return $this;
   }

   /**
    * Indique que la table pivot doit gérer les timestamps
    * 
    * @return $this
    */
   public function withTimestamps(): self
   {
      $this->withTimestamps = true;

      return $this->withPivot(['created_at', 'updated_at']);
   }

   /**
    * Définit les valeurs par défaut pour les attributs de la table pivot
    * 
    * @param array $attributes Attributs par défaut
    * @return $this
    */
   public function withPivotValues(array $attributes): self
   {
      $this->pivotDefaults = array_merge($this->pivotDefaults, $attributes);

      return $this;
   }

   /**
    * Récupère les résultats de la relation
    * 
    * @return Collection
    */
   public function getResults(): Collection
   {
      $results = $this->query->get();

      // Transformer les attributs pivot en objet
      foreach ($results as $model) {
         $this->hydratePivotAttributes($model);
      }

      return $results;
   }

   /**
    * Remplit les attributs pivot sur le modèle
    * 
    * @param Model $model Modèle à hydrater
    * @return void
    */
   protected function hydratePivotAttributes(Model $model): void
   {
      $pivotAttributes = [];

      // Extraire les attributs pivot du modèle
      foreach ($model->getAttributes() as $key => $value) {
         if (strpos($key, 'pivot_') === 0) {
            $pivotAttributes[substr($key, 6)] = $value;
            // Supprimer l'attribut pivot_ du modèle
            unset($model->{$key});
         }
      }

      // Définir l'objet pivot sur le modèle
      $model->setPivot(new Pivot($this, $model, $pivotAttributes, $this->table));
   }

   /**
    * Attache des modèles liés au modèle parent
    * 
    * @param mixed $ids Identifiants des modèles à attacher
    * @param array $attributes Attributs supplémentaires pour la table pivot
    * @return void
    */
   public function attach($ids, array $attributes = []): void
   {
      if ($ids instanceof Model) {
         $ids = [$ids->{$this->relatedKey}];
      } elseif ($ids instanceof Collection) {
         $ids = $ids->map(function ($model) {
            return $model->{$this->relatedKey};
         })->all();
      } elseif (!is_array($ids)) {
         $ids = [$ids];
      }

      // Fusionner les attributs avec les valeurs par défaut
      $attributes = array_merge($this->pivotDefaults, $attributes);

      // Préparer les données d'insertion
      $records = [];

      foreach ($ids as $id) {
         $record = [
            $this->foreignPivotKey => $this->parent->{$this->parentKey},
            $this->relatedPivotKey => $id
         ];

         // Ajouter les attributs supplémentaires
         foreach ($attributes as $key => $value) {
            $record[$key] = $value;
         }

         // Ajouter les timestamps si nécessaire
         if ($this->withTimestamps) {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $record['created_at'] = $now;
            $record['updated_at'] = $now;
         }

         $records[] = $record;
      }

      // Insertion dans la table pivot
      if (!empty($records)) {
         $columns = array_keys($records[0]);
         $placeholders = [];
         $bindings = [];

         foreach ($records as $record) {
            $recordPlaceholders = [];

            foreach ($columns as $column) {
               $recordPlaceholders[] = '?';
               $bindings[] = $record[$column];
            }

            $placeholders[] = '(' . implode(', ', $recordPlaceholders) . ')';
         }

         $query = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES " . implode(', ', $placeholders);

         Connection::getInstance()->getConnection()->prepare($query)->execute($bindings);
      }
   }

   /**
    * Détache des modèles liés du modèle parent
    * 
    * @param mixed $ids Identifiants des modèles à détacher (null pour tous)
    * @return int Nombre d'enregistrements supprimés
    */
   public function detach($ids = null): int
   {
      $query = "DELETE FROM {$this->table} WHERE {$this->foreignPivotKey} = ?";
      $bindings = [$this->parent->{$this->parentKey}];

      // Si des IDs spécifiques sont fournis, ajouter une condition supplémentaire
      if ($ids !== null) {
         if ($ids instanceof Model) {
            $ids = [$ids->{$this->relatedKey}];
         } elseif ($ids instanceof Collection) {
            $ids = $ids->map(function ($model) {
               return $model->{$this->relatedKey};
            })->all();
         } elseif (!is_array($ids)) {
            $ids = [$ids];
         }

         if (!empty($ids)) {
            $placeholders = implode(', ', array_fill(0, count($ids), '?'));
            $query .= " AND {$this->relatedPivotKey} IN ({$placeholders})";
            $bindings = array_merge($bindings, $ids);
         }
      }

      $stmt = Connection::getInstance()->getConnection()->prepare($query);
      $stmt->execute($bindings);

      return $stmt->rowCount();
   }

   /**
    * Synchronise les relations avec une liste d'identifiants
    * 
    * @param mixed $ids Identifiants des modèles à synchroniser
    * @param array $attributes Attributs supplémentaires pour la table pivot
    * @return array Tableau des modifications [attached, detached, updated]
    */
   public function sync($ids, array $attributes = []): array
   {
      if ($ids instanceof Collection) {
         $idsArray = [];
         foreach ($ids as $model) {
            $pivotAttributes = isset($model->pivot) ? (array) $model->pivot->getAttributes() : [];
            $idsArray[$model->{$this->relatedKey}] = array_merge($pivotAttributes, $attributes);
         }
         $ids = $idsArray;
      } elseif ($ids instanceof Model) {
         $ids = [$ids->{$this->relatedKey} => $attributes];
      } elseif (!is_array($ids)) {
         $ids = [$ids => $attributes];
      }

      // Format des identifiants doit être sous forme de tableau associatif ou simple
      if (isset($ids[0])) {
         $ids = array_fill_keys($ids, $attributes);
      }

      // Récupérer les relations existantes
      $currentRecords = $this->newPivotQuery()
         ->select([$this->relatedPivotKey])
         ->where($this->foreignPivotKey, $this->parent->{$this->parentKey})
         ->get();

      $current = [];
      foreach ($currentRecords as $record) {
         $current[$record->{$this->relatedPivotKey}] = true;
      }

      // Déterminer ce qui doit être attaché/détaché
      $detach = array_diff_key($current, $ids);
      $attach = array_diff_key($ids, $current);

      // Détacher les relations qui ne sont plus dans la liste
      if (count($detach) > 0) {
         $this->detach(array_keys($detach));
      }

      // Attacher les nouvelles relations
      if (count($attach) > 0) {
         $this->attachWithAttributes($attach);
      }

      return [
         'attached' => array_keys($attach),
         'detached' => array_keys($detach),
         'updated' => [] // L'implémentation actuelle ne met pas à jour les attributs existants
      ];
   }

   /**
    * Attache des modèles avec attributs personnalisés pour chaque modèle
    * 
    * @param array $attributes Tableau associatif [id => attributs]
    * @return void
    */
   protected function attachWithAttributes(array $attributes): void
   {
      $records = [];

      foreach ($attributes as $id => $pivotAttributes) {
         $record = [
            $this->foreignPivotKey => $this->parent->{$this->parentKey},
            $this->relatedPivotKey => $id
         ];

         // Ajouter les attributs supplémentaires
         foreach ($pivotAttributes as $key => $value) {
            $record[$key] = $value;
         }

         // Ajouter les timestamps si nécessaire
         if ($this->withTimestamps) {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $record['created_at'] = $now;
            $record['updated_at'] = $now;
         }

         $records[] = $record;
      }

      // Insertion dans la table pivot
      if (!empty($records)) {
         $columns = array_keys($records[0]);
         $placeholders = [];
         $bindings = [];

         foreach ($records as $record) {
            $recordPlaceholders = [];

            foreach ($columns as $column) {
               $recordPlaceholders[] = '?';
               $bindings[] = $record[$column];
            }

            $placeholders[] = '(' . implode(', ', $recordPlaceholders) . ')';
         }

         $query = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES " . implode(', ', $placeholders);

         Connection::getInstance()->getConnection()->prepare($query)->execute($bindings);
      }
   }

   /**
    * Crée un nouveau query builder pour la table pivot
    * 
    * @return PivotQueryBuilder
    */
   protected function newPivotQuery(): PivotQueryBuilder
   {
      // Utiliser une requête directe PDO pour la table pivot car ce n'est pas un modèle
      $pdo = Connection::getInstance()->getConnection();
      return new PivotQueryBuilder($pdo, $this->table);
   }
}
