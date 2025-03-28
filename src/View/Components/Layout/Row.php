<?php

declare(strict_types=1);

namespace IronFlow\View\Components\Layout;

class Row extends Layout
{
   protected int $rows = 1 ;
   protected array $gaps = ['4'];
   protected array $breakpoints = ['sm', 'md', 'lg', 'xl'];

   public function rows(int $rows): self
   {
      $this->rows = $rows;
      return $this;
   }

   public function gap(string|array $gap): self
   {
      $this->gaps = is_array($gap) ? $gap : [$gap];
      return $this;
   }

   public function breakpoints(array $breakpoints): self
   {
      $this->breakpoints = $breakpoints;
      return $this;
   }

   public function render(): string
   {
      $classes = ['flex flex-row'];

      if ($this->rows > 1) {
         $classes[] = 'flex-col';
      }

      return sprintf(
         '<div class="%s" %s>%s</div>',
         implode(' ', $classes),
         $this->renderAttributes(),
         $this->renderContent()
      );
   }
}
