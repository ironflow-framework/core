<?php

namespace IronFlow\Forms;

use IronFlow\Database\Collection;
use IronFlow\Forms\Components\Component;
use IronFlow\Forms\Themes\ThemeInterface;
use IronFlow\Forms\Themes\DefaultTheme;
use IronFlow\Forms\Themes\FloatingTheme;
use IronFlow\Forms\Themes\MaterialTheme;
use IronFlow\Forms\Themes\TailwindTheme;
use IronFlow\Validation\Validator;

class Form
{
   protected array $fields = [];
   protected array $data = [];

   protected ?Validator $validator = null;

   protected string $title = "Formulaire";
   protected string $icon = "";
   protected string $method = 'POST';
   protected string $action = '';

   protected string $theme = 'default';
   protected array $themes = [
      'default' => DefaultTheme::class,
      'floating' => FloatingTheme::class,
      'material' => MaterialTheme::class,
      'tailwind' => TailwindTheme::class,
   ];

   public function __construct(protected ?string $model = null) {
      $this->hidden('_token', csrf_token());
   }

   public function method(string $method): self
   {
      $this->method = strtoupper($method);
      return $this;
   }

   public function title(string $title): self
   {
      $this->title = $title;
      return $this;
   }

   public function icon(string $icon): self
   {
      $this->icon = $icon;
      return $this;
   }

   public function action(string $action): self
   {
      $this->action = $action;
      return $this;
   }

   public function whenEditing(?int $id = null): self
   {
      if ($id) {
         $this->hidden('id', (string) $id);
      }
      return $this;
   }

   public function theme(string $theme): self
   {
      if (!isset($this->themes[$theme])) {
         throw new \InvalidArgumentException("Theme {$theme} not found");
      }
      $this->theme = $theme;
      return $this;
   }

   public function input(string $name, string $label, array $attributes = []): self
   {
      $this->fields[] = new Components\Input($name, $label, $attributes);
      return $this;
   }

   public function textarea(string $name, string $label, array $attributes = []): self
   {
      $this->fields[] = new Components\Textarea($name, $label, $attributes);
      return $this;
   }

   public function select(string $name, string $label, array|Collection $options, array $attributes = []): self
   {
      $this->fields[] = new Components\Select($name, $label, $options, $attributes);
      return $this;
   }

   public function checkbox(string $name, string $label, array|Collection $choices = [], array $attributes = []): self
   {
      $this->fields[] = new Components\Checkbox($name, $label, $choices, $attributes);
      return $this;
   }

   public function radio(string $name, string $label, array|Collection $choices, array $attributes = []): self
   {
      $this->fields[] = new Components\Radio($name, $choices, $label, $attributes);
      return $this;
   }

   public function date(string $name, string $label, array $attributes = [], bool $showTime = false): self
   {
      $this->fields[] = new Components\DatePicker($name, $label, $attributes, $showTime);
      return $this;
   }

   public function color(string $name, string $label, array $attributes = []): self
   {
      $this->fields[] = new Components\ColorPicker($name, $label, $attributes);
      return $this;
   }


   public function file(string $name, string $label, array $attributes = [], array $accept = []): self
   {
      $this->fields[] = new Components\File($name, $label, $attributes, []);
      return $this;
   }

   public function hidden(string $name, string $value = '', array $attributes = []): self
   {
      $attributes['type']  = 'hidden';
      $attributes['value'] = $value;

      $this->fields[] = new Components\Input($name, '', $attributes);
      return $this;
   }


   public function button(string $text, array $attributes = []): self
   {
      $this->fields[] = new Components\Button($text, $attributes);
      return $this;
   }

   public function fill(array $data): self
   {
      $this->data = $data;
      return $this;
   }

   public function validator(Validator $validator): self
   {
      $this->validator = $validator;
      return $this;
   }

   public function render(): string
   {
      // Toujours s'assurer que le CSRF token est là
      if (!$this->hasCsrfToken()) {
         $this->hidden('_token', csrf_token());
      }

      $themeClass = $this->themes[$this->theme];
      /** @var ThemeInterface $theme */
      $theme = new $themeClass();

      return $theme->render($this);
   }

   protected function hasCsrfToken(): bool
   {
      foreach ($this->fields as $field) {
         if (method_exists($field, 'getName') && $field->getName() === '_token') {
            return true;
         }
      }
      return false;
   }


   public function getTitle(): string
   {
      return $this->title;
   }

   public function getIcon(): string
   {
      return $this->icon;
   }

   public function getFields(): array
   {
      return $this->fields;
   }

   public function getData(): array
   {
      return $this->data;
   }

   public function getMethod(): string
   {
      return $this->method;
   }

   public function getAction(): string
   {
      return $this->action;
   }

   public function getModel(): ?string
   {
      return $this->model;
   }

   public function getTheme(): ?string
   {
      return $this->theme;
   }

   public function hasTitle(): bool 
   {
      return $this->title !== null;
   }

   public function hasIcon(): bool
   {
      return $this->icon !== null;
   }

   public function __toString(): string
   {
      return $this->render();
   }
}
