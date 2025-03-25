<?php

declare(strict_types=1);

namespace Tests\View\Components\UI;

use IronFlow\View\Components\UI\Button;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
{
   public function testButtonRendersWithDefaultAttributes(): void
   {
      $button = new Button();
      $button->setContent('Click me');

      $html = $button->render();

      $this->assertStringContainsString('type="button"', $html);
      $this->assertStringContainsString('class="inline-flex items-center justify-center font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 bg-indigo-600 text-white hover:bg-indigo-700 focus:ring-indigo-500 px-4 py-2 text-sm"', $html);
      $this->assertStringContainsString('Click me', $html);
   }

   public function testButtonRendersWithDifferentVariants(): void
   {
      $variants = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];

      foreach ($variants as $variant) {
         $button = new Button();
         $button->variant($variant);
         $button->setContent('Click me');

         $html = $button->render();

         $this->assertStringContainsString($variant, $html);
      }
   }

   public function testButtonRendersWithDifferentSizes(): void
   {
      $sizes = ['xs', 'sm', 'md', 'lg', 'xl'];

      foreach ($sizes as $size) {
         $button = new Button();
         $button->size($size);
         $button->setContent('Click me');

         $html = $button->render();

         $this->assertStringContainsString($size, $html);
      }
   }

   public function testButtonRendersWithIcon(): void
   {
      $button = new Button();
      $button->setContent('Click me');
      $button->icon('<svg>...</svg>');

      $html = $button->render();

      $this->assertStringContainsString('<svg>...</svg>', $html);
      $this->assertStringContainsString('mr-2 -ml-1', $html);
   }

   public function testButtonRendersWithIconOnly(): void
   {
      $button = new Button();
      $button->icon('<svg>...</svg>');
      $button->iconOnly();

      $html = $button->render();

      $this->assertStringContainsString('<svg>...</svg>', $html);
      $this->assertStringNotContainsString('mr-2 -ml-1', $html);
      $this->assertStringNotContainsString('Click me', $html);
   }

   public function testButtonRendersWithFullWidth(): void
   {
      $button = new Button();
      $button->setContent('Click me');
      $button->fullWidth();

      $html = $button->render();

      $this->assertStringContainsString('w-full', $html);
   }

   public function testButtonRendersWithDisabledState(): void
   {
      $button = new Button();
      $button->setContent('Click me');
      $button->disabled();

      $html = $button->render();

      $this->assertStringContainsString('opacity-50 cursor-not-allowed', $html);
      $this->assertStringContainsString('disabled="disabled"', $html);
   }
}
