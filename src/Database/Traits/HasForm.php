<?php

namespace IronFlow\Database\Traits;

use IronFlow\Forms\Form;

trait HasForm
{
   public static function form(): Form
   {
      $formClass = static::getFormClass();

      return new $formClass(static::class);
   }

   public static function getFormClass(): string
   {
      // On cherche dans un attribut static $formClass si défini sur le modèle
      if (property_exists(static::class, 'formClass') && static::$formClass) {
         return static::$formClass;
      }

      // Sinon, on regarde s'il existe une classe Form dédiée dans App\Forms
      $defaultFormClass = 'App\\Forms\\' . class_basename(static::class) . 'Form';

      if (class_exists($defaultFormClass)) {
         return $defaultFormClass;
      }

      // Fallback : Form générique
      return Form::class;
   }
}
