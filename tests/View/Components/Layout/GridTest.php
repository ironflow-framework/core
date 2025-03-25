<?php

declare(strict_types=1);

namespace Tests\View\Components\Layout;

use IronFlow\View\Components\Layout\Grid;
use PHPUnit\Framework\TestCase;

class GridTest extends TestCase
{
   public function testGridRendersWithDefaultAttributes(): void
   {
      $grid = new Grid();
      $grid->setContent('Grid content');

      $html = $grid->render();

      $this->assertStringContainsString('class="grid grid-cols-12 gap-4"', $html);
      $this->assertStringContainsString('Grid content', $html);
   }

   public function testGridRendersWithCustomColumns(): void
   {
      $grid = new Grid();
      $grid->columns(6);
      $grid->setContent('Grid content');

      $html = $grid->render();

      $this->assertStringContainsString('class="grid grid-cols-6 gap-4"', $html);
   }

   public function testGridRendersWithCustomGap(): void
   {
      $grid = new Grid();
      $grid->gap('8');
      $grid->setContent('Grid content');

      $html = $grid->render();

      $this->assertStringContainsString('class="grid grid-cols-12 gap-8"', $html);
   }

   public function testGridRendersWithResponsiveGaps(): void
   {
      $grid = new Grid();
      $grid->gap(['4', '6', '8']);
      $grid->setContent('Grid content');

      $html = $grid->render();

      $this->assertStringContainsString('class="grid grid-cols-12 gap-4 sm:gap-6 md:gap-8"', $html);
   }

   public function testGridRendersWithCustomBreakpoints(): void
   {
      $grid = new Grid();
      $grid->breakpoints(['sm', 'lg']);
      $grid->setContent('Grid content');

      $html = $grid->render();

      $this->assertStringContainsString('class="grid grid-cols-12 gap-4"', $html);
   }

   public function testGridRendersWithCustomAttributes(): void
   {
      $grid = new Grid();
      $grid->withAttributes(['id' => 'main-grid', 'data-test' => 'test']);
      $grid->setContent('Grid content');

      $html = $grid->render();

      $this->assertStringContainsString('id="main-grid"', $html);
      $this->assertStringContainsString('data-test="test"', $html);
   }
}
