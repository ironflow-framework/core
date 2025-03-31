<?php

declare(strict_types=1);

namespace IronFlow\View\Components\UI;

use IronFlow\View\Component;

class Alert extends Component
{
   protected string $message;
   protected string $type;

   public function __construct(string $message, string $type = 'info')
   {
      $this->message = $message;
      $this->type = $type;
   }

   protected function getTypeClasses(): string
   {
      $types = [
         'info' => 'bg-blue-100 text-blue-700',
         'success' => 'bg-green-100 text-green-700',
         'warning' => 'bg-yellow-100 text-yellow-700',
         'danger' => 'bg-red-100 text-red-700',
      ];

      return $types[$this->type] ?? $types['info'];
   }

   public function render(): string
   {
      return sprintf('<div class="p-4 rounded %s">%s</div>', $this->getTypeClasses(), $this->message);
   }
}
