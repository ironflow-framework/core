<?php

namespace IronFlow\Tests\Core\Container;

use IronFlow\Core\Container\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
   private Container $container;

   protected function setUp(): void
   {
      $this->container = new Container();
   }

   /**
    * Test que le container peut enregistrer et récupérer une instance
    */
   public function testContainerCanBindAndResolveInstance(): void
   {
      $instance = new \stdClass();

      $this->container->bind('test', $instance);

      $resolved = $this->container->get('test');

      $this->assertSame($instance, $resolved);
   }

   /**
    * Test que le container peut enregistrer et récupérer une closure
    */
   public function testContainerCanBindAndResolveClosure(): void
   {
      $this->container->bind('test', function () {
         return new \stdClass();
      });

      $resolved1 = $this->container->get('test');
      $resolved2 = $this->container->get('test');

      $this->assertInstanceOf(\stdClass::class, $resolved1);
      $this->assertNotSame($resolved1, $resolved2);
   }

   /**
    * Test que le container peut enregistrer et récupérer un singleton
    */
   public function testContainerCanBindAndResolveSingleton(): void
   {
      $this->container->singleton('test', function () {
         return new \stdClass();
      });

      $resolved1 = $this->container->get('test');
      $resolved2 = $this->container->get('test');

      $this->assertInstanceOf(\stdClass::class, $resolved1);
      $this->assertSame($resolved1, $resolved2);
   }

   /**
    * Test que le container peut vérifier si une abstraction est liée
    */
   public function testContainerCanCheckIfBound(): void
   {
      $this->assertFalse($this->container->has('test'));

      $this->container->bind('test', function () {
         return new \stdClass();
      });

      $this->assertTrue($this->container->has('test'));
   }

   /**
    * Test que le container peut résoudre automatiquement une classe
    */
   public function testContainerCanResolveClassAutomatically(): void
   {
      $resolved = $this->container->get(TestClass::class);

      $this->assertInstanceOf(TestClass::class, $resolved);
   }

   /**
    * Test que le container peut résoudre une classe avec des dépendances
    */
   public function testContainerCanResolveClassWithDependencies(): void
   {
      $resolved = $this->container->get(TestClassWithDependency::class);

      $this->assertInstanceOf(TestClassWithDependency::class, $resolved);
      $this->assertInstanceOf(TestClass::class, $resolved->dependency);
   }
}

/**
 * Classe de test pour l'injection de dépendances automatique
 */
class TestClass
{
   public function __construct() {}
}

/**
 * Classe de test avec une dépendance
 */
class TestClassWithDependency
{
   public TestClass $dependency;

   public function __construct(TestClass $dependency)
   {
      $this->dependency = $dependency;
   }
}
