<?php

namespace Database\Seeders;

use App\Models\Product;
use Database\Factories\ProductFactory;

class ProductSeeder
{
    public function run(): void
    {
        Product::factory(10)->create();
    }
}