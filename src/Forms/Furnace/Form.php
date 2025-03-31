<?php

namespace IronFlow\Forms\Furnace;

abstract class Form
{
   protected array $fields = [];
   protected string $method = 'POST';
   protected string $action = '';
   protected array $attributes = [];
   protected array $components = [];
   protected array $rules = [];

   public function addField(string $name, string $type, array $options = []): void
   {
      $this->fields[$name] = [
         'type' => $type,
         'options' => $options
      ];
   }

   public function addComponent(Field $field): self
   {
      $this->components[$field->getName()] = $field;
      return $this;
   }

   public function addRule(string $name, string $rule): self
   {
      $this->rules[$name] = $rule;
      return $this;
   }

   public function setType(string $type): self
   {
      foreach ($this->components as $component) {
         $component->type($type);
      }
      return $this;
   }

   public function setRequired(bool $required = true): self
   {
      foreach ($this->components as $component) {
         $component->required($required);
      }
      return $this;
   }

   public function setValue(string $name, string $value): self
   {
      $this->components[$name]->value($value);
      return $this;
   }

   public function setOptions(array $options): self
   {
      foreach ($this->components as $component) {
         $component->options($options);
      }
      return $this;
   }

   public function setAttributes(array $attributes): self
   {
      foreach ($this->components as $component) {
         $component->attributes($attributes);
      }
      return $this;
   }

   public function setRules(array $rules): self
   {
      foreach ($this->components as $component) {
         $component->rules($rules);
      }
      return $this;
   }

   public function setError(string $name, string $error): self
   {
      $this->components[$name]->error($error);
      return $this;
   }

   public function setPlaceholder(string $placeholder): self
   {
      foreach ($this->components as $component) {
         $component->placeholder($placeholder);
      }
      return $this;
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

      foreach ($this->components as $component) {
         $html .= $this->renderComponent($component);
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

   protected function renderComponent(Field $component): string
   {
      $html = sprintf(
         '<div class="form-group">
                <label for="%s">%s</label>',
         $component->getName(),
         $component->getLabel()
      );
      $html .= $component->render();
      $html .= '</div>';
      return $html;
   }
}
