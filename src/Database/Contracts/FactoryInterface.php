<?php 

namespace IronFlow\Database\Contracts;

interface FactoryInterface
{
   public function defineDefaults(): void;
   public function create(array $attributes = [], ?string $state = null): object;
   public function count(int $count): self;
   public function make(array $attributes = [], ?string $state = null): object;
}