<?php

namespace IronFlow\Furnace;

abstract class Form
{
   protected array $fields = [];
   protected string $method = 'POST';
   protected string $action = '';
   protected array $attributes = [];

   public function addField(string $name, string $type, array $options = []): void
   {
      $this->fields[$name] = [
         'type' => $type,
         'options' => $options
      ];
   }

   public function setMethod(string $method): self
   {
      $this->method = $method;
      return $this;
   }

   public function setAction(string $action): self
   {
      $this->action = $action;
      return $this;
   }

   public function setAttribute(string $name, string $value): self
   {
      $this->attributes[$name] = $value;
      return $this;
   }

   public function render(): string
   {
      $html = sprintf(
         '<form method="%s" action="%s" %s>',
         $this->method,
         $this->action,
         $this->renderAttributes()
      );

      foreach ($this->fields as $name => $field) {
         $html .= $this->renderField($name, $field);
      }

      $html .= '</form>';
      return $html;
   }

   protected function renderAttributes(): string
   {
      $attributes = [];
      foreach ($this->attributes as $name => $value) {
         $attributes[] = sprintf('%s="%s"', $name, htmlspecialchars($value));
      }
      return implode(' ', $attributes);
   }

   protected function renderField(string $name, array $field): string
   {
      $type = $field['type'];
      $options = $field['options'];

      $label = $options['label'] ?? ucfirst($name);
      $required = isset($options['required']) && $options['required'] ? 'required' : '';
      $placeholder = isset($options['placeholder']) ? sprintf('placeholder="%s"', htmlspecialchars($options['placeholder'])) : '';

      $html = sprintf(
         '<div class="form-group">
                <label for="%s">%s</label>',
         $name,
         $label
      );

      switch ($type) {
         case 'textarea':
            $rows = $options['rows'] ?? 3;
            $html .= sprintf(
               '<textarea name="%s" id="%s" %s %s rows="%d"></textarea>',
               $name,
               $name,
               $required,
               $placeholder,
               $rows
            );
            break;
         default:
            $html .= sprintf(
               '<input type="%s" name="%s" id="%s" %s %s>',
               $type,
               $name,
               $name,
               $required,
               $placeholder
            );
      }

      $html .= '</div>';
      return $html;
   }
}
