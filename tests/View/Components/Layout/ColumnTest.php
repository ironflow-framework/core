<?php

declare(strict_types=1);

namespace Tests\View\Components\Layout;

use IronFlow\View\Components\Layout\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
   public function testColumnRendersWithDefaultAttributes(): void
   {
      $column = new Column();
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-12"', $html);
      $this->assertStringContainsString('Column content', $html);
   }

   public function testColumnRendersWithSpan(): void
   {
      $column = new Column();
      $column->span(6);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-6"', $html);
   }

   public function testColumnRendersWithResponsiveSpans(): void
   {
      $column = new Column();
      $column->span(['12', '6', '4']);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-12 sm:col-span-6 md:col-span-4"', $html);
   }

   public function testColumnRendersWithOffset(): void
   {
      $column = new Column();
      $column->offset(3);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-12 col-start-4"', $html);
   }

   public function testColumnRendersWithResponsiveOffsets(): void
   {
      $column = new Column();
      $column->offset(['0', '3', '6']);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-12 sm:col-start-4 md:col-start-7"', $html);
   }

   public function testColumnRendersWithOrder(): void
   {
      $column = new Column();
      $column->order(2);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-12 order-2"', $html);
   }

   public function testColumnRendersWithResponsiveOrders(): void
   {
      $column = new Column();
      $column->order(['1', '2', '3']);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('class="col-span-12 order-1 sm:order-2 md:order-3"', $html);
   }

   public function testColumnRendersWithCustomAttributes(): void
   {
      $column = new Column();
      $column->withAttributes(['id' => 'main-column', 'data-test' => 'test']);
      $column->setContent('Column content');

      $html = $column->render();

      $this->assertStringContainsString('id="main-column"', $html);
      $this->assertStringContainsString('data-test="test"', $html);
   }
}
