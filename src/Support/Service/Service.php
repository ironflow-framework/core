<?php

namespace IronFlow\Support\Service;

use IronFlow\Application\Application;
use IronFlow\Support\Service\Contracts\ServiceInterface;

abstract class Service implements ServiceInterface
{
   protected Application $app;

   public function __construct(Application $app)
   {
      $this->app = $app;
   }

   public function register(): void
   {
      // TODO: Implement register() method.
   }

   public function boot(): void
   {
      // TODO: Implement boot() method.
   }
   
   
}