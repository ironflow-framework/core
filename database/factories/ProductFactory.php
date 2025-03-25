<?php

namespace Database\Factories;

use IronFlow\Database\Factories\Factory;
use App\Models\Product;
use Faker\Generator as FakerGenerator;

class ProductFactory extends Factory
{
    public function definition(FakerGenerator $fake): array
    {
        return [
            'name' => $fake->word,
        ];
    }
}