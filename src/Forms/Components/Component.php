<?php

namespace IronFlow\Forms\Components;

abstract class Component
{
   protected array $options = [];
   protected ?string $value = null;
   protected array $errors = [];

   public function __construct(
      protected string $name,
      protected string $label,
      array $options = []
   ) {
      $this->options = $options;
   }

   public function getName(): string
   {
      return $this->name;
   }

   public function getLabel(): string
   {
      return $this->label;
   }

   public function getValue(): ?string
   {
      return $this->value;
   }

   public function setValue(?string $value): self
   {
      $this->value = $value;
      return $this;
   }

   public function getErrors(): array
   {
      return $this->errors;
   }

   public function setErrors(array $errors): self
   {
      $this->errors = $errors;
      return $this;
   }

   public function hasError(): bool
   {
      return !empty($this->errors);
   }

   public function getOption(string $key, $default = null)
   {
      return $this->options[$key] ?? $default;
   }

   abstract public function render(): string;
}
