<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Validation\Validator as ValidatorInstance;

class Validator extends Facade
{
   /**
    * Récupère l'instance du Validator
    */
   protected static function getFacadeInstance(): ValidatorInstance
   {
      return new ValidatorInstance([], []);
   }

   /**
    * Crée une nouvelle instance du Validator
    */
   public static function make(array $data, array $rules): ValidatorInstance
   {
      return new ValidatorInstance($data, $rules);
   }

   /**
    * Valide des données
    */
   public static function validate(array $data, array $rules): bool
   {
      return static::make($data, $rules)->validate();
   }

   /**
    * Récupère les erreurs de validation
    */
   public static function errors(array $data, array $rules): array
   {
      $validator = static::make($data, $rules);
      $validator->validate();
      return $validator->getErrors();
   }

   /**
    * Récupère la première erreur de validation
    */
   public static function firstError(array $data, array $rules): ?string
   {
      $validator = static::make($data, $rules);
      $validator->validate();
      return $validator->getFirstError();
   }

   /**
    * Vérifie s'il y a des erreurs de validation
    */
   public static function hasErrors(array $data, array $rules): bool
   {
      $validator = static::make($data, $rules);
      $validator->validate();
      return $validator->hasErrors();
   }
}
