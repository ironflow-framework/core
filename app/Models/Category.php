<?php 

namespace App\Models;

use IronFlow\Database\Model;
use IronFlow\Database\Traits\HasForm;

class Category extends Model
{
   use HasForm;
   protected static string $table = 'categories';

   protected array $fillable = ['name', 'description'];

   public function products()
   {
      return $this->hasMany(Product::class);
   }
   
}
