<?php

declare(strict_types=1);

namespace IronFlow\View\Components\UI;

use IronFlow\View\Component;

class Card extends Component
{
   protected ?string $title = null;
   protected ?string $description = null;
   protected ?string $image = null;
   protected ?string $link = null;
   protected ?string $linkText = null;
   protected ?string $linkIcon = null;
   protected string $variant = 'default';

   public function __construct(array $attributes = [])
   {
      parent::__construct($attributes);
   }

   public function title(string $title): self
   {
      $this->title = $title;
      return $this;
   }

   public function description(string $description): self
   {
      $this->description = $description;
      return $this;
   }

   public function image(string $image): self
   {
      $this->image = $image;
      return $this;
   }

   public function link(string $link): self
   {
      $this->link = $link;
      return $this;
   }

   public function linkText(string $linkText): self
   {
      $this->linkText = $linkText;
      return $this;
   }

   public function linkIcon(string $linkIcon): self
   {
      $this->linkIcon = $linkIcon;
      return $this;
   }

   public function variant(string $variant): self
   {
      $this->variant = $variant;
      return $this;
   }

   protected function getVariantClasses(): string
   {
      $variants = [
         'default' => 'bg-white shadow rounded-lg',
         'bordered' => 'border border-gray-300 shadow-sm rounded-lg',
         'elevated' => 'shadow-lg rounded-lg',
      ];

      return $variants[$this->variant] ?? $variants['default'];
   }

   public function render(): string
   {
      $classes = ['p-4', $this->getVariantClasses()];

      return sprintf(
         '<div class="%s">
                %s
                %s
                %s
            </div>',
         implode(' ', $classes),
         $this->image ? "<img src='{$this->image}' class='rounded-t-lg' />" : '',
         $this->title ? "<h3 class='text-lg font-semibold'>{$this->title}</h3>" : '',
         $this->description ? "<p class='text-gray-600'>{$this->description}</p>" : ''
      );
   }
}
