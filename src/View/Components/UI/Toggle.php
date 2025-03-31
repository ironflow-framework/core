<?php

declare(strict_types=1);

namespace IronFlow\View\Components\UI;

use IronFlow\View\Component;

class Toggle extends Component
{
   protected string $name;
   protected string $label;
   protected bool $checked = false;
   protected ?string $value = null;
   protected bool $required = false;
   protected bool $disabled = false;
   protected ?string $helpText = null;
   protected ?string $id = null;
   protected string $size = 'md';
   protected string $color = 'indigo';
   protected ?string $onText = null;
   protected ?string $offText = null;
   protected bool $labelRight = true;

   public function __construct(array $attributes = [])
   {
      parent::__construct($attributes);

      $this->name = $attributes['name'] ?? '';
      $this->label = $attributes['label'] ?? '';
      $this->checked = $attributes['checked'] ?? false;
      $this->value = $attributes['value'] ?? 'on';
      $this->id = $attributes['id'] ?? 'toggle-' . uniqid();
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

   public function checked(bool $checked = true): self
   {
      $this->checked = $checked;
      return $this;
   }

   public function value(?string $value): self
   {
      $this->value = $value;
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

   public function size(string $size): self
   {
      $validSizes = ['sm', 'md', 'lg'];
      if (!in_array($size, $validSizes)) {
         throw new \InvalidArgumentException("Invalid toggle size: {$size}");
      }
      $this->size = $size;
      return $this;
   }

   public function color(string $color): self
   {
      $validColors = ['indigo', 'blue', 'green', 'red', 'yellow', 'purple', 'pink'];
      if (!in_array($color, $validColors)) {
         throw new \InvalidArgumentException("Invalid toggle color: {$color}");
      }
      $this->color = $color;
      return $this;
   }

   public function onText(?string $onText): self
   {
      $this->onText = $onText;
      return $this;
   }

   public function offText(?string $offText): self
   {
      $this->offText = $offText;
      return $this;
   }

   public function labelRight(bool $labelRight = true): self
   {
      $this->labelRight = $labelRight;
      return $this;
   }

   protected function getSwitchAttributes(): array
   {
      $attributes = [
         'type' => 'checkbox',
         'name' => $this->name,
         'id' => $this->id,
         'value' => $this->value,
         'role' => 'switch',
         'aria-checked' => $this->checked ? 'true' : 'false',
         'class' => 'sr-only',
      ];

      if ($this->checked) {
         $attributes['checked'] = 'checked';
      }

      if ($this->required) {
         $attributes['required'] = 'required';
      }

      if ($this->disabled) {
         $attributes['disabled'] = 'disabled';
      }

      return array_merge($attributes, $this->attributes);
   }

   protected function getToggleSizeClasses(): string
   {
      $sizes = [
         'sm' => [
            'wrapper' => 'h-5 w-9',
            'switch' => 'h-4 w-4',
            'translate' => 'translate-x-4',
            'text' => 'text-xs',
         ],
         'md' => [
            'wrapper' => 'h-6 w-11',
            'switch' => 'h-5 w-5',
            'translate' => 'translate-x-5',
            'text' => 'text-sm',
         ],
         'lg' => [
            'wrapper' => 'h-7 w-14',
            'switch' => 'h-6 w-6',
            'translate' => 'translate-x-7',
            'text' => 'text-sm',
         ],
      ];

      return json_encode($sizes[$this->size]);
   }

   protected function getToggleColorClasses(): string
   {
      $colors = [
         'indigo' => [
            'active' => 'bg-indigo-600',
            'inactive' => 'bg-gray-200',
         ],
         'blue' => [
            'active' => 'bg-blue-600',
            'inactive' => 'bg-gray-200',
         ],
         'green' => [
            'active' => 'bg-green-600',
            'inactive' => 'bg-gray-200',
         ],
         'red' => [
            'active' => 'bg-red-600',
            'inactive' => 'bg-gray-200',
         ],
         'yellow' => [
            'active' => 'bg-yellow-600',
            'inactive' => 'bg-gray-200',
         ],
         'purple' => [
            'active' => 'bg-purple-600',
            'inactive' => 'bg-gray-200',
         ],
         'pink' => [
            'active' => 'bg-pink-600',
            'inactive' => 'bg-gray-200',
         ],
      ];

      return json_encode($colors[$this->color]);
   }

   public function render(): string
   {
      $helpTextHtml = '';
      if ($this->helpText) {
         $helpTextHtml = sprintf(
            '<p class="mt-1 text-sm text-gray-500">%s</p>',
            $this->helpText
         );
      }

      $labelHtml = '';
      if ($this->label) {
         $labelHtml = sprintf(
            '<span class="text-sm font-medium text-gray-700%s">%s%s</span>',
            $this->disabled ? ' opacity-60' : '',
            $this->label,
            $this->required ? ' <span class="text-red-500">*</span>' : ''
         );
      }

      // Create toggle switch with JavaScript for interactions
      $toggleHtml = sprintf(
         '<label for="%s" class="relative inline-flex items-center%s">
                <input %s />
                <div 
                    class="toggle-track relative inline-flex flex-shrink-0 cursor-pointer rounded-full transition-colors ease-in-out duration-200 border-2 border-transparent focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500%s"
                    data-size=\'%s\'
                    data-color=\'%s\'
                    data-disabled="%s"
                >
                    %s
                    <span
                        aria-hidden="true"
                        class="toggle-switch pointer-events-none inline-block rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200%s"
                    ></span>
                    %s
                </div>
            </label>',
         $this->id,
         $this->disabled ? ' cursor-not-allowed' : '',
         $this->renderAttributes($this->getSwitchAttributes()),
         $this->disabled ? ' opacity-60 cursor-not-allowed' : '',
         $this->getToggleSizeClasses(),
         $this->getToggleColorClasses(),
         $this->disabled ? 'true' : 'false',
         $this->offText ? '<span class="toggle-off-text sr-only">' . $this->offText . '</span>' : '',
         $this->disabled ? ' cursor-not-allowed' : '',
         $this->onText ? '<span class="toggle-on-text sr-only">' . $this->onText . '</span>' : ''
      );

      // Create wrapper with flexbox for label positioning
      $wrapperHtml = '';
      if ($this->labelRight) {
         $wrapperHtml = sprintf(
            '<div class="flex items-center">
                    %s
                    <div class="ml-3">%s</div>
                </div>',
            $toggleHtml,
            $labelHtml
         );
      } else {
         $wrapperHtml = sprintf(
            '<div class="flex items-center justify-between">
                    <div class="mr-3">%s</div>
                    %s
                </div>',
            $labelHtml,
            $toggleHtml
         );
      }

      // JavaScript to handle toggle functionality
      $jsHtml = sprintf(
         '<script>
                document.addEventListener("DOMContentLoaded", function() {
                    const toggleInput = document.getElementById("%s");
                    if (!toggleInput) return;
                    
                    const track = toggleInput.nextElementSibling;
                    const switchEl = track.querySelector(".toggle-switch");
                    
                    const size = JSON.parse(track.getAttribute("data-size"));
                    const color = JSON.parse(track.getAttribute("data-color"));
                    const disabled = track.getAttribute("data-disabled") === "true";
                    
                    // Apply sizes
                    track.classList.add(size.wrapper);
                    switchEl.classList.add(size.switch);
                    
                    // Apply initial state
                    if (toggleInput.checked) {
                        track.classList.add(color.active);
                        switchEl.classList.add(size.translate);
                    } else {
                        track.classList.add(color.inactive);
                    }
                    
                    // Update toggle state when input changes
                    toggleInput.addEventListener("change", function() {
                        if (this.checked) {
                            track.classList.remove(color.inactive);
                            track.classList.add(color.active);
                            switchEl.classList.add(size.translate);
                        } else {
                            track.classList.remove(color.active);
                            track.classList.add(color.inactive);
                            switchEl.classList.remove(size.translate);
                        }
                    });
                });
            </script>',
         $this->id
      );

      return sprintf(
         '<div class="toggle-container">
                %s
                %s
                %s
            </div>',
         $wrapperHtml,
         $helpTextHtml,
         $jsHtml
      );
   }
}
