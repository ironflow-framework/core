<?php

declare(strict_types=1);

namespace IronFlow\View\Components\Layout;

class Container extends Layout
{
   protected bool $fluid = false;
   protected string $maxWidth = '7xl';

   public function fluid(bool $fluid = true): self
   {
      $this->fluid = $fluid;
      return $this;
   }

   public function maxWidth(string $width): self
   {
      $this->maxWidth = $width;
      return $this;
   }

   public function render(): string
   {
      $classes = ['mx-auto px-4 sm:px-6 lg:px-8'];

      if (!$this->fluid) {
         $classes[] = 'max-w-' . $this->maxWidth;
      }

      return sprintf(
         '<div class="%s" %s>%s</div>',
         implode(' ', $classes),
         $this->renderAttributes(),
         $this->renderContent()
      );
   }
}
