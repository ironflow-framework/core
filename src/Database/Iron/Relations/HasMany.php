<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Collection;
use IronFlow\Database\Model;

/**
 * Relation HasMany (Un à Plusieurs)
 * 
 * Cette relation indique qu'un modèle possède plusieurs modèles associés qui peuvent
 * être accédés via une clé étrangère sur les modèles associés.
 */
class HasMany extends Relation
{
   /**
    * Clé étrangère sur les modèles associés
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
    * @param string $foreignKey Clé étrangère sur les modèles associés
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
    * @return Collection
    */
   public function getResults(): Collection
   {
      return $this->query->get();
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
    * Crée plusieurs nouveaux modèles associés
    * 
    * @param array $records Tableaux d'attributs pour les nouveaux modèles
    * @return Collection
    */
   public function createMany(array $records): Collection
   {
      $models = new Collection();

      foreach ($records as $attributes) {
         $models->push($this->create($attributes));
      }

      return $models;
   }

   /**
    * Trouve un modèle existant ou en crée un nouveau
    * 
    * @param array $attributes Attributs pour la recherche
    * @param array $values Attributs supplémentaires pour la création
    * @return Model
    */
   public function firstOrCreate(array $attributes, array $values = []): Model
   {
      // Cloner la requête actuelle pour ne pas perturber la requête principale
      $query = clone $this->query;

      // Ajouter les conditions de recherche
      foreach ($attributes as $key => $value) {
         $query->where($key, $value);
      }

      // Rechercher le modèle
      $model = $query->first();

      // Si le modèle n'existe pas, le créer
      if (!$model) {
         $model = $this->create(array_merge($attributes, $values));
      }

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
      $model = $this->firstOrCreate($attributes);

      $model->fill($values);
      $model->save();

      return $model;
   }

   /**
    * Associe plusieurs modèles au parent
    * 
    * @param array $models Modèles à associer
    * @return Collection
    */
   public function saveMany(array $models): Collection
   {
      $collection = new Collection();

      foreach ($models as $model) {
         $model->{$this->foreignKey} = $this->parent->{$this->localKey};
         $model->save();

         $collection->push($model);
      }

      return $collection;
   }
}
