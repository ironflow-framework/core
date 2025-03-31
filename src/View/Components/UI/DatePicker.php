<?php

declare(strict_types=1);

namespace IronFlow\View\Components\UI;

use IronFlow\View\Component;

class DatePicker extends Component
{
   protected string $name;
   protected string $label;
   protected ?string $value = null;
   protected string $format = 'Y-m-d';
   protected ?string $placeholder = null;
   protected bool $required = false;
   protected bool $disabled = false;
   protected bool $readonly = false;
   protected ?string $minDate = null;
   protected ?string $maxDate = null;
   protected ?string $helpText = null;
   protected ?string $id = null;

   public function __construct(array $attributes = [])
   {
      parent::__construct($attributes);

      $this->name = $attributes['name'] ?? '';
      $this->label = $attributes['label'] ?? '';
      $this->value = $attributes['value'] ?? null;
      $this->id = $attributes['id'] ?? 'datepicker-' . uniqid();
   }

   public function name(string $name): self
   {
      $this->name = $name;
      return $this;
   }

   public function label(string $label): self
   {
      $this->label = $label;
      return $this;
   }

   public function value(?string $value): self
   {
      $this->value = $value;
      return $this;
   }

   public function format(string $format): self
   {
      $this->format = $format;
      return $this;
   }

   public function placeholder(?string $placeholder): self
   {
      $this->placeholder = $placeholder;
      return $this;
   }

   public function required(bool $required = true): self
   {
      $this->required = $required;
      return $this;
   }

   public function disabled(bool $disabled = true): self
   {
      $this->disabled = $disabled;
      return $this;
   }

   public function readonly(bool $readonly = true): self
   {
      $this->readonly = $readonly;
      return $this;
   }

   public function minDate(?string $minDate): self
   {
      $this->minDate = $minDate;
      return $this;
   }

   public function maxDate(?string $maxDate): self
   {
      $this->maxDate = $maxDate;
      return $this;
   }

   public function helpText(?string $helpText): self
   {
      $this->helpText = $helpText;
      return $this;
   }

   public function id(string $id): self
   {
      $this->id = $id;
      return $this;
   }

   protected function getInputAttributes(): array
   {
      $attributes = [
         'type' => 'text',
         'name' => $this->name,
         'id' => $this->id,
         'class' => 'block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm',
         'data-datepicker' => 'true',
         'data-format' => $this->format,
      ];

      if ($this->value) {
         $attributes['value'] = $this->value;
      }

      if ($this->placeholder) {
         $attributes['placeholder'] = $this->placeholder;
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      if ($this->disabled) {
         $attributes['disabled'] = 'disabled';
      }

      if ($this->readonly) {
         $attributes['readonly'] = 'readonly';
      }

      if ($this->minDate) {
         $attributes['data-min-date'] = $this->minDate;
      }

      if ($this->maxDate) {
         $attributes['data-max-date'] = $this->maxDate;
      }

      return array_merge($attributes, $this->attributes);
   }

   public function render(): string
   {
      $labelHtml = '';
      if ($this->label) {
         $labelHtml = sprintf(
            '<label for="%s" class="block text-sm font-medium text-gray-700 mb-1">%s%s</label>',
            $this->id,
            $this->label,
            $this->required ? ' <span class="text-red-500">*</span>' : ''
         );
      }

      $helpTextHtml = '';
      if ($this->helpText) {
         $helpTextHtml = sprintf(
            '<p class="mt-2 text-sm text-gray-500">%s</p>',
            $this->helpText
         );
      }

      $inputHtml = sprintf(
         '<div class="relative">
                <input %s />
                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>',
         $this->renderAttributes($this->getInputAttributes())
      );

      return sprintf(
         '<div class="datepicker-container">
                %s
                %s
                %s
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const datepicker = document.getElementById("%s");
                        if (datepicker && typeof flatpickr !== "undefined") {
                            flatpickr(datepicker, {
                                dateFormat: "%s",
                                minDate: %s,
                                maxDate: %s,
                                allowInput: true,
                            });
                        }
                    });
                </script>
            </div>',
         $labelHtml,
         $inputHtml,
         $helpTextHtml,
         $this->id,
         $this->format,
         $this->minDate ? "'{$this->minDate}'" : 'null',
         $this->maxDate ? "'{$this->maxDate}'" : 'null'
      );
   }
}
