<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Model;

/**
 * Relation HasOne (Un à Un)
 * 
 * Cette relation indique qu'un modèle possède un modèle associé qui peut être accédé
 * via une clé étrangère sur le modèle associé.
 */
class HasOne extends Relation
{
   /**
    * Clé étrangère sur le modèle associé
    * 
    * @var string
    */
   protected string $foreignKey;

   /**
    * Clé locale sur le modèle parent
    * 
    * @var string
    */
   protected string $localKey;

   /**
    * Constructeur
    * 
    * @param Model $related Modèle cible de la relation
    * @param Model $parent Modèle parent de la relation
    * @param string $foreignKey Clé étrangère sur le modèle associé
    * @param string $localKey Clé locale sur le modèle parent
    */
   public function __construct(Model $related, Model $parent, string $foreignKey, string $localKey)
   {
      $this->related = $related;
      $this->parent = $parent;
      $this->foreignKey = $foreignKey;
      $this->localKey = $localKey;

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

      // Ajouter la contrainte pour lier le modèle parent
      $this->query->where($this->foreignKey, $this->parent->{$this->localKey});
   }

   /**
    * Récupère les résultats de la relation
    * 
    * @return Model|null
    */
   public function getResults(): ?Model
   {
      return $this->query->first();
   }

   /**
    * Associe un modèle enfant au parent
    * 
    * @param Model $model Modèle à associer
    * @return Model
    */
   public function associate(Model $model): Model
   {
      $model->{$this->foreignKey} = $this->parent->{$this->localKey};
      $model->save();

      return $model;
   }

   /**
    * Dissocie le modèle enfant du parent
    * 
    * @return bool
    */
   public function dissociate(): bool
   {
      $model = $this->getResults();

      if ($model) {
         $model->{$this->foreignKey} = null;
         return $model->save();
      }

      return false;
   }

   /**
    * Crée un nouveau modèle associé
    * 
    * @param array $attributes Attributs du nouveau modèle
    * @return Model
    */
   public function create(array $attributes = []): Model
   {
      $attributes[$this->foreignKey] = $this->parent->{$this->localKey};

      $relatedClass = get_class($this->related);
      $model = $relatedClass::create($attributes);

      return $model;
   }

   /**
    * Met à jour ou crée un modèle associé
    * 
    * @param array $attributes Attributs pour la recherche
    * @param array $values Attributs pour la mise à jour ou création
    * @return Model
    */
   public function updateOrCreate(array $attributes, array $values = []): Model
   {
      // Créer une requête avec les conditions de recherche
      $query = $this->related::query();

      // Ajouter les conditions de recherche
      foreach ($attributes as $key => $value) {
         $query->where($key, $value);
      }

      $model = $query->first();

      if (!$model) {
         return $this->create(array_merge($attributes, $values));
      }

      $model->fill($values);
      $model->save();

      return $model;
   }
}
