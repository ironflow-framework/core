<?php 

namespace IronFlow\Database\Contracts;

use Faker\Generator as FakerGenerator;
use IronFlow\Database\Model;

interface FactoryInterface
{
   public function definition(FakerGenerator $faker): array;
   public function create(array $override = []): Model;
   public function count(int $count): self;
   public function make(array $override = []): Model;
}