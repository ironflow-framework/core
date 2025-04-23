<?php

namespace IronFlow\Tests\Core\Application;

use IronFlow\Core\Application\Application;
use IronFlow\Core\Container\ContainerInterface;
use IronFlow\Routing\RouterInterface;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
   private Application $app;
   private string $basePath;

   protected function setUp(): void
   {
      $this->basePath = dirname(dirname(dirname(dirname(__DIR__))));
      $this->app = Application::getInstance($this->basePath);
   }

   /**
    * Test que l'instance de l'application est un singleton
    */
   public function testApplicationIsSingleton(): void
   {
      $app1 = Application::getInstance();
      $app2 = Application::getInstance();

      $this->assertSame($app1, $app2);
   }

   /**
    * Test que l'application a un container
    */
   public function testApplicationHasContainer(): void
   {
      $container = $this->app->getContainer();

      $this->assertInstanceOf(ContainerInterface::class, $container);
   }

   /**
    * Test que l'application a un router
    */
   public function testApplicationHasRouter(): void
   {
      $router = $this->app->getRouter();

      $this->assertInstanceOf(RouterInterface::class, $router);
   }

   /**
    * Test que l'application a un chemin de base
    */
   public function testApplicationHasBasePath(): void
   {
      $basePath = $this->app->getBasePath();

      $this->assertIsString($basePath);
      $this->assertNotEmpty($basePath);
   }

   /**
    * Test que l'application peut être configurée avec des routes
    */
   public function testApplicationCanBeConfiguredWithRoutes(): void
   {
      $webRoutes = 'routes/web.php';
      $apiRoutes = 'routes/api.php';

      $app = $this->app->withRouter($webRoutes, $apiRoutes);

      $this->assertSame($this->app, $app);
   }

   /**
    * Test que l'application peut être configurée avec des providers
    */
   public function testApplicationCanBeConfiguredWithProviders(): void
   {
      $providers = [
         'IronFlow\Providers\AppServiceProvider',
         'IronFlow\Providers\RouteServiceProvider'
      ];

      $app = $this->app->withProvider($providers);

      $this->assertSame($this->app, $app);
   }

   /**
    * Test que l'application peut être démarrée
    */
   public function testApplicationCanBeBootstrapped(): void
   {
      // Cette méthode ne devrait pas lever d'exception
      $this->app->bootstrap();

      $this->assertTrue(true);
   }
}
