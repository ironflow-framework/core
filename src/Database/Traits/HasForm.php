<?php

namespace IronFlow\Database\Traits;

use IronFlow\Forms\Form;

trait HasForm
{
   public static function form(): Form
   {
      return new Form(static::class);
   }
}
