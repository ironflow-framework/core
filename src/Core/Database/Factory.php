<?php

namespace IronFlow\Core\Database;

use Faker\Factory as FakerFactory;

abstract class Factory
{
    protected $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create('fr_FR');
    }

    abstract public function definition(): array;

    public function create(array $attributes = []): Model
    {
        $data = array_merge($this->definition(), $attributes);
        return static::model()::create($data);
    }

    abstract public static function model(): string;
}
