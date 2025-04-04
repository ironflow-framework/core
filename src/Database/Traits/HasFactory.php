<?php

namespace IronFlow\Database\Traits;

use IronFlow\Database\Model;

trait HasFactory
{
   public static function factory()
   {
      return new class extends Model
      {
         protected $table = 'users';
      };
   }
}
