<?php

namespace IronFlow\Tests\Routing;

use IronFlow\Core\Container\Container;
use IronFlow\Http\Request;
use IronFlow\Routing\Router;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
   private Router $router;

   protected function setUp(): void
   {
      $this->router = new Router(new Container());
   }

   /**
    * Test que le router peut ajouter une route GET
    */
   public function testRouterCanAddGetRoute(): void
   {
      $this->router->get('/test', function () {
         return 'test';
      });

      $this->assertCount(1, $this->router->getRoutes());
   }

   /**
    * Test que le router peut ajouter une route POST
    */
   public function testRouterCanAddPostRoute(): void
   {
      $this->router->post('/test', function () {
         return 'test';
      });

      $this->assertCount(1, $this->router->getRoutes());
   }

   /**
    * Test que le router peut ajouter une route avec des paramètres
    */
   public function testRouterCanAddRouteWithParameters(): void
   {
      $this->router->get('/test/{id}', function ($id) {
         return $id;
      });

      $this->assertCount(1, $this->router->getRoutes());
   }

   /**
    * Test que le router peut ajouter une route avec un middleware
    */
   public function testRouterCanAddRouteWithMiddleware(): void
   {
      $this->router->get('/test', function () {
         return 'test';
      })->middleware('auth');

      $routes = $this->router->getRoutes();
      $route = $routes[0];

      $this->assertContains('auth', $route->getMiddleware());
   }

   /**
    * Test que le router peut ajouter une route avec un nom
    */
   public function testRouterCanAddRouteWithName(): void
   {
      $this->router->get('/test', function () {
         return 'test';
      })->name('test');

      $routes = $this->router->getRoutes();
      $route = $routes[0];

      $this->assertEquals('test', $route->getName());
   }

   /**
    * Test que le router peut trouver une route par son nom
    */
   public function testRouterCanFindRouteByName(): void
   {
      $this->router->get('/test', function () {
         return 'test';
      })->name('test');

      $route = $this->router->getRouteByName('test');

      $this->assertNotNull($route);
      $this->assertEquals('/test', $route->getPath());
   }

   /**
    * Test que le router peut faire correspondre une requête à une route
    */
   public function testRouterCanMatchRequestToRoute(): void
   {
      $this->router->get('/test', function () {
         return 'test';
      });

      $request = new Request();
      $request->setMethod('GET');
      $request->setUri('/test');

      $route = $this->router->match([$request->method()], $request->url(), function () {
         return 'test';
      });

      $this->assertNotNull($route);
   }

   /**
    * Test que le router peut faire correspondre une requête à une route avec des paramètres
    */
   public function testRouterCanMatchRequestToRouteWithParameters(): void
   {
      $this->router->get('/test/{id}', function ($id) {
         return $id;
      });

      $request = new Request();
      $request->setMethod('GET');
      $request->setUri('/test/123');

      $route = $this->router->match([$request->method()], $request->url(), function () {
         return 'test';
      });

      $this->assertNotNull($route);
      $this->assertEquals(['id' => '123'], $route->getPatterns());
   }
}
