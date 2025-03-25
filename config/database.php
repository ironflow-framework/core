<?php

declare(strict_types=1);

return [
   'default' => env('DB_CONNECTION', 'mysql'),

   'connections' => [
      'mysql' => [
         'driver' => 'mysql',
         'host' => env('DB_HOST', '127.0.0.1'),
         'port' => env('DB_PORT', '3306'),
         'database' => env('DB_DATABASE', 'ironflow'),
         'username' => env('DB_USERNAME', 'root'),
         'password' => env('DB_PASSWORD', ''),
         'charset' => 'utf8mb4',
         'collation' => 'utf8mb4_unicode_ci',
         'prefix' => '',
         'strict' => true,
         'engine' => null,
      ],

      'sqlite' => [
         'driver' => 'sqlite',
         'database' => env('DB_DATABASE', database_path('database.sqlite')),
         'prefix' => '',
      ],

      'pgsql' => [
         'driver' => 'pgsql',
         'host' => env('DB_HOST', '127.0.0.1'),
         'port' => env('DB_PORT', '5432'),
         'database' => env('DB_DATABASE', 'ironflow'),
         'username' => env('DB_USERNAME', 'root'),
         'password' => env('DB_PASSWORD', ''),
         'charset' => 'utf8',
         'prefix' => '',
         'schema' => 'public',
         'sslmode' => 'prefer',
      ],
   ],

   'migrations' => 'migrations',

   'redis' => [
      'client' => env('REDIS_CLIENT', 'phpredis'),
      'default' => [
         'host' => env('REDIS_HOST', '127.0.0.1'),
         'password' => env('REDIS_PASSWORD', null),
         'port' => env('REDIS_PORT', 6379),
         'database' => env('REDIS_DB', 0),
      ],
      'cache' => [
         'host' => env('REDIS_HOST', '127.0.0.1'),
         'password' => env('REDIS_PASSWORD', null),
         'port' => env('REDIS_PORT', 6379),
         'database' => env('REDIS_CACHE_DB', 1),
      ],
   ],
];
