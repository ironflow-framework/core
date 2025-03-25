<?php

declare(strict_types=1);

namespace Database\Seeder;

use IronFlow\Database\Connection;
use IronFlow\Database\Seeder\Seeder;

class DatabaseSeeder extends Seeder
{
   public function __construct(Connection $connection)
   {
      parent::__construct($connection);
   }

   public function run(): void
   {
      // Les seeders seront ajoutés ici
      // $this->call(UserSeeder::class);
      // $this->call(PostSeeder::class);
      // etc.
   }
}
