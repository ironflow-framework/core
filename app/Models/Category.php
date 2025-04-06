<?php

namespace App\Models;

use IronFlow\Database\Iron\Relations\HasMany;
use IronFlow\Database\Model;
use IronFlow\Database\Traits\HasForm;
use IronFlow\Database\Traits\HasFactory;

class Category extends Model
{
   use HasForm;
   use HasFactory;

   protected static string $table = 'categories';

   protected array $fillable = [
      'name',
   ];

   protected array $casts = [
      'created_at' => 'datetime',
      'updated_at' => 'datetime'
   ];

   public function products(): HasMany
   {
      return $this->hasMany(Product::class);
   }
}
