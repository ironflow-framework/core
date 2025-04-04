<?php

namespace IronFlow\Forms\Components;

class Button extends Component
{
   protected string $type = 'submit';
   protected ?string $icon = null;

   public function __construct(string $text, array $options = [])
   {
      parent::__construct('', $text, $options);

      $this->type = $options['type'] ?? 'submit';
      $this->icon = $options['icon'] ?? null;
   }

   public function render(): string
   {
      $attributes = [
         'type' => $this->type,
         'class' => $this->getOption('class', 'btn btn-primary'),
      ];

      $html = '<button ' . $this->buildAttributes($attributes) . '>';

      if ($this->icon) {
         $html .= '<i class="' . $this->icon . '"></i> ';
      }

      $html .= $this->label;
      $html .= '</button>';

      return $html;
   }

   protected function buildAttributes(array $attributes): string
   {
      return implode(' ', array_map(
         fn($key, $value) => $value === true ? $key : "$key=\"$value\"",
         array_keys($attributes),
         $attributes
      ));
   }
}
