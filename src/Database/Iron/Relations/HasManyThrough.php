<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Collection;
use IronFlow\Database\Model;

/**
 * Relation HasManyThrough (Plusieurs à travers)
 * 
 * Cette relation fournit un raccourci pratique pour accéder à des relations distantes
 * via un modèle intermédiaire.
 */
class HasManyThrough extends Relation
{
   /**
    * Modèle intermédiaire
    * 
    * @var Model
    */
   protected Model $through;

   /**
    * Clé étrangère sur le modèle intermédiaire
    * 
    * @var string
    */
   protected string $firstKey;

   /**
    * Clé étrangère sur le modèle cible
    * 
    * @var string
    */
   protected string $secondKey;

   /**
    * Clé locale du modèle parent
    * 
    * @var string
    */
   protected string $localKey;

   /**
    * Clé locale du modèle intermédiaire
    * 
    * @var string
    */
   protected string $secondLocalKey;

   /**
    * Constructeur
    * 
    * @param Model $related Modèle cible
    * @param Model $parent Modèle parent
    * @param Model $through Modèle intermédiaire
    * @param string $firstKey Clé étrangère sur le modèle intermédiaire
    * @param string $secondKey Clé étrangère sur le modèle cible
    * @param string $localKey Clé locale du modèle parent
    * @param string $secondLocalKey Clé locale du modèle intermédiaire
    */
   public function __construct(
      Model $related,
      Model $parent,
      Model $through,
      string $firstKey,
      string $secondKey,
      string $localKey,
      string $secondLocalKey
   ) {
      $this->related = $related;
      $this->parent = $parent;
      $this->through = $through;
      $this->firstKey = $firstKey;
      $this->secondKey = $secondKey;
      $this->localKey = $localKey;
      $this->secondLocalKey = $secondLocalKey;

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

      // Récupérer les noms de table
      $relatedTable = $this->related->getTable();
      $throughTable = $this->through->getTable();

      // Joindre les tables
      $this->query->join(
         $throughTable,
         $relatedTable . '.' . $this->secondKey,
         '=',
         $throughTable . '.' . $this->secondLocalKey
      );

      // Ajouter la contrainte pour lier au modèle parent
      $this->query->where($throughTable . '.' . $this->firstKey, $this->parent->{$this->localKey});

      // Sélectionner uniquement les colonnes du modèle cible
      $this->query->select($relatedTable . '.*');
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
}
