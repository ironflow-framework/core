<?php

declare(strict_types=1);

namespace Tests\View\Components\Layout;

use IronFlow\View\Components\Layout\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
   public function testContainerRendersWithDefaultAttributes(): void
   {
      $container = new Container();
      $container->setContent('Container content');

      $html = $container->render();

      $this->assertStringContainsString('class="container mx-auto px-4 max-w-7xl"', $html);
      $this->assertStringContainsString('Container content', $html);
   }

   public function testContainerRendersAsFluid(): void
   {
      $container = new Container();
      $container->fluid();
      $container->setContent('Container content');

      $html = $container->render();

      $this->assertStringContainsString('class="container-fluid mx-auto px-4"', $html);
   }

   public function testContainerRendersWithCustomMaxWidth(): void
   {
      $container = new Container();
      $container->maxWidth('5xl');
      $container->setContent('Container content');

      $html = $container->render();

      $this->assertStringContainsString('class="container mx-auto px-4 max-w-5xl"', $html);
   }

   public function testContainerRendersWithCustomAttributes(): void
   {
      $container = new Container();
      $container->withAttributes(['id' => 'main-container', 'data-test' => 'test']);
      $container->setContent('Container content');

      $html = $container->render();

      $this->assertStringContainsString('id="main-container"', $html);
      $this->assertStringContainsString('data-test="test"', $html);
   }
}
