<?php

namespace IronFlow\Forms;

use IronFlow\Forms\Components\Component;
use IronFlow\Forms\Themes\ThemeInterface;
use IronFlow\Forms\Themes\DefaultTheme;
use IronFlow\Forms\Themes\FloatingTheme;
use IronFlow\Forms\Themes\MaterialTheme;
use IronFlow\Forms\Themes\TailwindTheme;

class Form
{
   protected array $fields = [];
   protected array $data = [];
   protected string $method = 'POST';
   protected string $action = '';
   protected string $theme = 'default';
   protected array $themes = [
      'default' => DefaultTheme::class,
      'floating' => FloatingTheme::class,
      'material' => MaterialTheme::class,
      'tailwind' => TailwindTheme::class,
   ];

   public function __construct(protected ?string $model = null) {}

   public function method(string $method): self
   {
      $this->method = strtoupper($method);
      return $this;
   }

   public function action(string $action): self
   {
      $this->action = $action;
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

   public function input(string $name, string $label, array $options = []): self
   {
      $this->fields[] = new Components\Input($name, $label, $options);
      return $this;
   }

   public function textarea(string $name, string $label, array $options = []): self
   {
      $this->fields[] = new Components\Textarea($name, $label, $options);
      return $this;
   }

   public function select(string $name, string $label, array $options = []): self
   {
      $this->fields[] = new Components\Select($name, $label, $options);
      return $this;
   }

   public function checkbox(string $name, string $label, array $options = []): self
   {
      $this->fields[] = new Components\Checkbox($name, $label, $options);
      return $this;
   }

   public function radio(string $name, string $label, array $options = []): self
   {
      $this->fields[] = new Components\Radio($name, $label, $options);
      return $this;
   }

   public function date(string $name, string $label, array $options = [], bool $showTime = false): self
   {
      $this->fields[] = new Components\DatePicker($name, $label, $options, $showTime);
      return $this;
   }

   public function color(string $name, string $label, array $options = []): self
   {
      $this->fields[] = new Components\ColorPicker($name, $label, $options);
      return $this;
   }


   public function file(string $name, string $label, array $options = [], array $accept = []): self
   {
      $this->fields[] = new Components\File($name, $label, $options, []);
      return $this;
   }

   public function button(string $text, array $options = []): self
   {
      $this->fields[] = new Components\Button($text, $options);
      return $this;
   }

   public function fill(array $data): self
   {
      $this->data = $data;
      return $this;
   }

   public function render(): string
   {
      $themeClass = $this->themes[$this->theme];
      /** @var ThemeInterface $theme */
      $theme = new $themeClass();

      return $theme->render($this);
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
}
