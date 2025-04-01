<?php 

namespace App\Models;

use IronFlow\Database\Model;

class Category extends Model
{
   protected static string $table = 'categories';

   protected array $fillable = ['name', 'description'];

   public function products()
   {
      return $this->hasMany(Product::class);
   }
   
}
