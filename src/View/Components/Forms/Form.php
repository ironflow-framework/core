<?php

declare(strict_types=1);

namespace IronFlow\View\Components\Forms;

use IronFlow\Forms\HasForm;
use IronFlow\View\Components\Component;
use IronFlow\Database\Model;
use IronFlow\Forms\Components\Checkbox;
use IronFlow\Forms\Components\ColorPicker;
use IronFlow\Forms\Components\DatePicker;
use IronFlow\Forms\Components\File;
use IronFlow\Forms\Components\Input;
use IronFlow\Forms\Components\Radio;
use IronFlow\Forms\Components\Select;
use IronFlow\Forms\Components\Textarea;
use IronFlow\Support\Helpers;

class Form extends Component
{
   protected Model $model;
   protected string $action = '';
   protected string $method = 'POST';
   protected array $fields = [];
   protected bool $hasValidation = false;

   public function __construct(string $model)
   {
      parent::__construct([]);
      $this->model = new $model;
      $this->hasValidation = in_array(HasForm::class, Helpers::classUsesRecursive($model));
   }

   public function action(string $action): self
   {
      $this->action = $action;
      return $this;
   }

   public function method(string $method): self
   {
      $this->method = strtoupper($method);
      return $this;
   }

   public function input(string $name, string $label = '', string $type = 'text'): Input
   {
      $input = new Input($name, $label);
      $input->type($type);

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $input->withError($error);
         }
      }

      $this->fields[] = $input;
      return $input;
   }

   public function textarea(string $name, string $label = ''): Textarea
   {
      $textarea = new Textarea($name, $label);

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $textarea->withError($error);
         }
      }

      $this->fields[] = $textarea;
      return $textarea;
   }

   public function select(string $name, string $label = '', array $options = []): Select
   {
      $select = new Select($name, $label);
      if (!empty($options)) {
         $select->options($options);
      }

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $select->withError($error);
         }
      }

      $this->fields[] = $select;
      return $select;
   }

   public function checkbox(string $name, string $label = '', array $options = []): Checkbox
   {
      $checkbox = new Checkbox($name, $label);
      if (!empty($options)) {
         $checkbox->options($options);
      }

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $checkbox->withError($error);
         }
      }

      $this->fields[] = $checkbox;
      return $checkbox;
   }

   public function radio(string $name, string $label = '', array $options = []): Radio
   {
      $radio = new Radio($name, $label);
      if (!empty($options)) {
         $radio->options($options);
      }

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $radio->withError($error);
         }
      }

      $this->fields[] = $radio;
      return $radio;
   }

   public function file(string $name, string $label = ''): File
   {
      $file = new File($name, $label);

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $file->withError($error);
         }
      }

      $this->fields[] = $file;
      return $file;
   }

   public function date(string $name, string $label = ''): DatePicker
   {
      $date = new DatePicker($name, $label);

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $date->withError($error);
         }
      }

      $this->fields[] = $date;
      return $date;
   }

   public function color(string $name, string $label = ''): ColorPicker
   {
      $color = new ColorPicker($name, $label);

      if ($this->hasValidation) {
         /** @var HasForm $model */
         $error = $this->model->getFieldError($name);
         if ($error) {
            $color->withError($error);
         }
      }

      $this->fields[] = $color;
      return $color;
   }

   public function render(): string
   {
      $template = '<form action="%s" method="%s" class="space-y-4">';

      if ($this->method === 'PUT' || $this->method === 'DELETE') {
         $template .= '<input type="hidden" name="_method" value="' . $this->method . '">';
         $this->method = 'POST';
      }

      if ($this->hasValidation) {
         /** @var HasForm $model */
         if ($this->model->hasFormErrors()) {
            $template .= '<div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
               <div class="flex">
                  <div class="flex-shrink-0">
                     <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                     </svg>
                  </div>
                  <div class="ml-3">
                     <p class="text-sm text-red-700">
                        Veuillez corriger les erreurs ci-dessous.
                     </p>
                  </div>
               </div>
            </div>';
         }
      }

      $template .= '%s'; // Contenu du formulaire
      $template .= '</form>';

      return sprintf($template, $this->action, $this->method, $this->renderFields());
   }

   protected function renderFields(): string
   {
      return implode("\n", array_map(function ($field) {
         return $field->render();
      }, $this->fields));
   }
}
