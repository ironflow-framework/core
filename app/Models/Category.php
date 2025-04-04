<?php

namespace App\Models;

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
      'description',
      'parent_id',
      'is_active',
      'type'
   ];

   protected array $casts = [
      'is_active' => 'boolean',
      'created_at' => 'datetime',
      'updated_at' => 'datetime'
   ];

   public function products()
   {
      return $this->hasMany(Product::class);
   }

   public function parent()
   {
      return $this->belongsTo(Category::class, 'parent_id');
   }

   public function children()
   {
      return $this->hasMany(Category::class, 'parent_id');
   }
}
