<?php

namespace App\Models;

use IronFlow\Database\Factories\HasFactory;
use IronFlow\Database\Model;
use IronFlow\Furnace\Traits\HasForm;

class Product extends Model
{
    use HasFactory;
    use HasForm;
    
    protected $table = 'products';
    
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id'
    ];
    
    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products');
    }
}