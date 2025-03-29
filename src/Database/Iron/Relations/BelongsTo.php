<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Model;

/**
 * Relation BelongsTo (Appartient à)
 * 
 * Cette relation est l'inverse de HasOne ou HasMany.
 * Elle indique qu'un modèle appartient à un autre modèle.
 */
class BelongsTo extends Relation
{
   /**
    * Clé étrangère sur le modèle enfant
    * 
    * @var string
    */
   protected string $foreignKey;

   /**
    * Clé du propriétaire sur le modèle parent
    * 
    * @var string
    */
   protected string $ownerKey;

   /**
    * Constructeur
    * 
    * @param Model $related Modèle cible de la relation (parent)
    * @param Model $child Modèle enfant
    * @param string $foreignKey Clé étrangère sur le modèle enfant
    * @param string $ownerKey Clé du propriétaire sur le modèle parent
    */
   public function __construct(Model $related, Model $child, string $foreignKey, string $ownerKey)
   {
      $this->related = $related;
      $this->parent = $child; // Le parent dans le contexte de la relation est l'enfant au niveau base de données
      $this->foreignKey = $foreignKey;
      $this->ownerKey = $ownerKey;

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

      // Ajouter la contrainte sur la clé du propriétaire
      $this->query->where($this->ownerKey, $this->parent->{$this->foreignKey});
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
    * Associe un modèle parent au modèle enfant
    * 
    * @param Model|null $model Modèle parent à associer
    * @return Model Le modèle enfant
    */
   public function associate(?Model $model): Model
   {
      $foreignKey = $this->foreignKey;

      if ($model) {
         $this->parent->{$foreignKey} = $model->{$this->ownerKey};
      } else {
         $this->parent->{$foreignKey} = null;
      }

      return $this->parent;
   }

   /**
    * Dissocie le modèle parent du modèle enfant
    * 
    * @return Model Le modèle enfant
    */
   public function dissociate(): Model
   {
      return $this->associate(null);
   }
}
