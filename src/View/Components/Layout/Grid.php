<?php

declare(strict_types=1);

namespace IronFlow\View\Components\Layout;

class Grid extends Layout
{
   protected int $columns = 12;
   protected array $gaps = ['4'];
   protected array $breakpoints = ['sm', 'md', 'lg', 'xl'];

   public function columns(int $columns): self
   {
      $this->columns = $columns;
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
      $classes = ['grid'];

      // Ajout des classes de gap
      foreach ($this->gaps as $gap) {
         $classes[] = 'gap-' . $gap;
      }

      // Ajout des classes de colonnes
      $classes[] = 'grid-cols-' . $this->columns;

      return sprintf(
         '<div class="%s" %s>%s</div>',
         implode(' ', $classes),
         $this->renderAttributes(),
         $this->renderContent()
      );
   }
}
