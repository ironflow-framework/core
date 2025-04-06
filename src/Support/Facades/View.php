<?php

namespace IronFlow\Support\Facades;

use IronFlow\Core\Application\Application;
use IronFlow\View\ViewInterface;

class View extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return 'view';
   }

   public static function render(string $name, array $data = [])
   {
      return Application::getInstance()->getContainer()->get('view')::getInstance()->render($name, $data);
   }
}