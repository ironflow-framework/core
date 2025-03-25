<?php

namespace App\Models;

use IronFlow\Database\Factories\HasFactory;
use IronFlow\Database\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
}