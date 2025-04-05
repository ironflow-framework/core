<?php

namespace IronFlow\Forms\Components;

class ColorPicker extends Component
{
   protected string $type = 'color';
   protected ?string $placeholder = null;
   protected bool $required = false;
   protected ?string $pattern = null;
   protected array $presetColors = [];
   protected bool $showAlpha = false;

   public function __construct(string $name, string $label, array $options = [])
   {
      parent::__construct($name, $label, $options);

      $this->placeholder = $options['placeholder'] ?? null;
      $this->required = $options['required'] ?? false;
      $this->pattern = $options['pattern'] ?? null;
      $this->presetColors = $options['preset'] ?? [];
      $this->showAlpha = $options['alpha'] ?? false;
   }

   /**
    * Récuperer l'attribut name
    *
    * @return string
    */
   public function getName(): string
   {
      return $this->name;
   }

   /**
    * Rendu du composant
    *
    * @return string
    */
   public function render(): string
   {
      $attributes = [
         'type' => $this->type,
         'name' => $this->name,
         'id' => $this->name,
         'class' => 'h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300',
      ];

      if ($this->value !== null) {
         $attributes['value'] = $this->value;
      }

      if ($this->placeholder) {
         $attributes['placeholder'] = $this->placeholder;
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      if ($this->pattern) {
         $attributes['pattern'] = $this->pattern;
      }

      if ($this->showAlpha) {
         $attributes['data-alpha'] = $this->showAlpha;
      }

      $presetColorsHtml = '';
      if (!empty($this->presetColors)) {
         $presetColorsHtml = '<div class="mt-2 flex flex-wrap gap-2">';
         foreach ($this->presetColors as $color) {
            $presetColorsHtml .= sprintf(
               '<button type="button" class="h-8 w-8 rounded-full border-2 border-gray-300" style="background-color: %s" data-color="%s"></button>',
               htmlspecialchars($color),
               htmlspecialchars($color)
            );
         }
         $presetColorsHtml .= '</div>';
      }

      $html = '<div class="form-group">';
      $html .= '<label for="' . $this->name . '">' . $this->label . '</label>';
      $html .= '<input ' . $this->buildAttributes($attributes) . '>';
      $html .= $presetColorsHtml;

      if ($this->getError()) {
         $html .= '<div class="error-message">' . $this->getError() . '</div>';
      }

      $html .= '</div>';

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
