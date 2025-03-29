<?php

namespace IronFlow\Database\Iron\Relations;

use IronFlow\Database\Model;

/**
 * Classe Pivot représentant la table pivot dans une relation plusieurs-à-plusieurs
 */
class Pivot
{
   /**
    * Attributs de la table pivot
    * 
    * @var array
    */
   protected array $attributes = [];

   /**
    * La relation parent
    * 
    * @var BelongsToMany
    */
   protected BelongsToMany $parent;

   /**
    * Le modèle lié
    * 
    * @var Model
    */
   protected Model $related;

   /**
    * Nom de la table pivot
    * 
    * @var string
    */
   protected string $table;

   /**
    * Constructeur
    * 
    * @param BelongsToMany $parent Relation parent
    * @param Model $related Modèle lié
    * @param array $attributes Attributs de la table pivot
    * @param string $table Nom de la table pivot
    */
   public function __construct(BelongsToMany $parent, Model $related, array $attributes, string $table)
   {
      $this->parent = $parent;
      $this->related = $related;
      $this->attributes = $attributes;
      $this->table = $table;
   }

   /**
    * Obtient tous les attributs de la table pivot
    * 
    * @return array
    */
   public function getAttributes(): array
   {
      return $this->attributes;
   }

   /**
    * Accès magique aux attributs
    * 
    * @param string $key Nom de l'attribut
    * @return mixed
    */
   public function __get(string $key)
   {
      return $this->attributes[$key] ?? null;
   }

   /**
    * Définition magique des attributs
    * 
    * @param string $key Nom de l'attribut
    * @param mixed $value Valeur de l'attribut
    */
   public function __set(string $key, $value): void
   {
      $this->attributes[$key] = $value;
   }

   /**
    * Vérifie si un attribut existe
    * 
    * @param string $key Nom de l'attribut
    * @return bool
    */
   public function __isset(string $key): bool
   {
      return isset($this->attributes[$key]);
   }
}
