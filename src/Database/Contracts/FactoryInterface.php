<?php 

namespace IronFlow\Database\Contracts;

use Faker\Generator as FakerGenerator;

interface FactoryInterface
{
  public function definition(FakerGenerator $fake): array;
   public function create(array $override = []): array;
   public function count(int $count): self;
   public function make(array $override = []): array;
}