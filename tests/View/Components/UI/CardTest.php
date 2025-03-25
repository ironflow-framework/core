<?php

declare(strict_types=1);

namespace Tests\View\Components\UI;

use IronFlow\View\Components\UI\Card;
use PHPUnit\Framework\TestCase;

class CardTest extends TestCase
{
   public function testCardRendersWithDefaultAttributes(): void
   {
      $card = new Card();
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('class="bg-white rounded-lg border border-gray-200"', $html);
      $this->assertStringContainsString('Card content', $html);
   }

   public function testCardRendersWithTitle(): void
   {
      $card = new Card();
      $card->title('Card Title');
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('Card Title', $html);
      $this->assertStringContainsString('text-lg font-semibold text-gray-900', $html);
   }

   public function testCardRendersWithSubtitle(): void
   {
      $card = new Card();
      $card->subtitle('Card Subtitle');
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('Card Subtitle', $html);
      $this->assertStringContainsString('text-sm text-gray-500', $html);
   }

   public function testCardRendersWithImage(): void
   {
      $card = new Card();
      $card->image('https://example.com/image.jpg');
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('src="https://example.com/image.jpg"', $html);
      $this->assertStringContainsString('class="w-full h-48 object-cover rounded-t-lg"', $html);
   }

   public function testCardRendersWithFooter(): void
   {
      $card = new Card();
      $card->footer('Card Footer');
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('Card Footer', $html);
      $this->assertStringContainsString('class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg"', $html);
   }

   public function testCardRendersWithActions(): void
   {
      $card = new Card();
      $card->addAction('Action 1', '/action1');
      $card->addAction('Action 2', '/action2');
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('Action 1', $html);
      $this->assertStringContainsString('Action 2', $html);
      $this->assertStringContainsString('href="/action1"', $html);
      $this->assertStringContainsString('href="/action2"', $html);
   }

   public function testCardRendersWithHoverEffect(): void
   {
      $card = new Card();
      $card->hover();
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('hover:shadow-md transition-shadow duration-200', $html);
   }

   public function testCardRendersWithoutShadow(): void
   {
      $card = new Card();
      $card->shadow(false);
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringNotContainsString('shadow-sm', $html);
   }

   public function testCardRendersWithCustomPadding(): void
   {
      $card = new Card();
      $card->padding('p-8');
      $card->setContent('Card content');

      $html = $card->render();

      $this->assertStringContainsString('class="p-8"', $html);
   }
}
