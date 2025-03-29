<?php

declare(strict_types=1);

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Collection;
use IronFlow\Database\Model;
use IronFlow\Database\Iron\Query\Builder;

/**
 * Classe de base pour toutes les relations
 */
abstract class Relation
{
   /**
    * Le modèle cible de la relation
    * 
    * @var Model
    */
   protected Model $related;

   /**
    * Le modèle parent de la relation
    * 
    * @var Model
    */
   protected Model $parent;

   /**
    * Le query builder pour la relation
    * 
    * @var Builder
    */
   protected Builder $query;

   /**
    * Récupère les résultats de la relation
    * 
    * @return Collection|Model|null
    */
   abstract public function getResults();

   /**
    * Initialise le builder de requête pour la relation
    * 
    * @return void
    */
   abstract protected function initQuery(): void;

   /**
    * Exécute la requête pour obtenir les résultats
    * 
    * @return Collection
    */
   public function get(): Collection
   {
      return $this->query->get();
   }

   /**
    * Récupère le premier résultat de la relation
    * 
    * @return Model|null
    */
   public function first(): ?Model
   {
      return $this->query->first();
   }

   /**
    * Compte le nombre de résultats
    * 
    * @return int
    */
   public function count(): int
   {
      return $this->query->count();
   }

   /**
    * Ajoute une clause where à la requête
    * 
    * @param string $column Colonne
    * @param mixed $operator Opérateur ou valeur
    * @param mixed|null $value Valeur (optionnelle)
    * @return $this
    */
   public function where(string $column, $operator = null, $value = null): self
   {
      $this->query->where($column, $operator, $value);
      return $this;
   }

   /**
    * Retourner l'existance des données
    *
    * @return bool
    */
    public function exists(): bool
    {
      return $this->query->exists();
    }

   /**
    * Ajoute une clause order by à la requête
    * 
    * @param string $column Colonne
    * @param string $direction Direction (asc/desc)
    * @return $this
    */
   public function orderBy(string $column, string $direction = 'asc'): self
   {
      $this->query->orderBy($column, $direction);
      return $this;
   }

   /**
    * Définit une limite à la requête
    * 
    * @param int $limit Limite
    * @return $this
    */
   public function limit(int $limit): self
   {
      $this->query->limit($limit);
      return $this;
   }

   /**
    * Obtient le builder de requête
    * 
    * @return Builder
    */
   public function getQuery(): Builder
   {
      return $this->query;
   }

   /**
    * Obtient le modèle parent
    * 
    * @return Model
    */
   public function getParent(): Model
   {
      return $this->parent;
   }

   /**
    * Obtient le modèle cible
    * 
    * @return Model
    */
   public function getRelated(): Model
   {
      return $this->related;
   }
}
