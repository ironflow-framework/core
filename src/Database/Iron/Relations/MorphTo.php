<?php

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Model;

class MorphTo extends Relation {


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
   public function initQuery(): void
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

   

  

}
