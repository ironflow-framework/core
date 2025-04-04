<?php

declare(strict_types=1);

namespace IronFlow\View\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use IronFlow\Support\Facades\Route;

class RouteExtension extends AbstractExtension
{
   public function getFunctions(): array
   {
      return [
         new TwigFunction('route', [$this, 'route']),
         new TwigFunction('asset', [$this, 'asset']),
      ];
   }

   public function route(string $name, array $parameters = []): string
   {
      return Route::url($name, $parameters);
   }

   public function asset(string $path): string
   {
      return '/assets/' . ltrim($path, '/');
   }
}
