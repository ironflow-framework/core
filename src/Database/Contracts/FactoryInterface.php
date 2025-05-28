<?php 

namespace IronFlow\Database\Contracts;

/**
 * Interface pour les factories
 */
interface FactoryInterface
{
    public function definition(): array;
    public function make(array $attributes = []): object;
    public function create(array $attributes = []): object;
}