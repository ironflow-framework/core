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

    protected $fillable = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}