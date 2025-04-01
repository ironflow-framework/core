<?php 

namespace App\Models;

use IronFlow\Database\Model;

class Category extends Model
{
   protected $table = 'categories';

   protected $fillable = ['name', 'description'];

   public function products()
   {
      return $this->hasMany(Product::class);
   }
   
}
