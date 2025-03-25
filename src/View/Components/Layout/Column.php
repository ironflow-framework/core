<?php

declare(strict_types=1);

namespace IronFlow\View\Components\Layout;

class Column extends Layout
{
   protected array $spans = [];
   protected array $offsets = [];
   protected array $orders = [];

   public function span(int|array $spans): self
   {
      if (is_array($spans)) {
         foreach ($spans as $breakpoint => $span) {
            $this->spans[$breakpoint] = $span;
         }
      } else {
         $this->spans['default'] = $spans;
      }
      return $this;
   }

   public function offset(int|array $offsets): self
   {
      if (is_array($offsets)) {
         foreach ($offsets as $breakpoint => $offset) {
            $this->offsets[$breakpoint] = $offset;
         }
      } else {
         $this->offsets['default'] = $offsets;
      }
      return $this;
   }

   public function order(int|array $orders): self
   {
      if (is_array($orders)) {
         foreach ($orders as $breakpoint => $order) {
            $this->orders[$breakpoint] = $order;
         }
      } else {
         $this->orders['default'] = $orders;
      }
      return $this;
   }

   public function render(): string
   {
      $classes = [];

      // Ajout des classes de span
      foreach ($this->spans as $breakpoint => $span) {
         if ($breakpoint === 'default') {
            $classes[] = 'col-span-' . $span;
         } else {
            $classes[] = $breakpoint . ':col-span-' . $span;
         }
      }

      // Ajout des classes d'offset
      foreach ($this->offsets as $breakpoint => $offset) {
         if ($breakpoint === 'default') {
            $classes[] = 'col-start-' . ($offset + 1);
         } else {
            $classes[] = $breakpoint . ':col-start-' . ($offset + 1);
         }
      }

      // Ajout des classes d'ordre
      foreach ($this->orders as $breakpoint => $order) {
         if ($breakpoint === 'default') {
            $classes[] = 'order-' . $order;
         } else {
            $classes[] = $breakpoint . ':order-' . $order;
         }
      }

      return sprintf(
         '<div class="%s" %s>%s</div>',
         implode(' ', $classes),
         $this->renderAttributes(),
         $this->renderContent()
      );
   }
}
