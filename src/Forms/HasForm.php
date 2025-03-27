<?php

declare(strict_types=1);

namespace IronFlow\Forms;

use IronFlow\View\Components\Forms\Form;
use IronFlow\Validation\Validator;

trait HasForm
{
   protected array $formRules = [];
   protected array $formMessages = [];
   protected array $formErrors = [];

   public static function form(): Form
   {
      return new Form(static::class);
   }

   public function getForm(): Form
   {
      return (require_once(app_path('Components/Forms/') . class_basename($this)));
   }

   public function getFormRules(): array
   {
      return $this->formRules;
   }

   public function getFormMessages(): array
   {
      return $this->formMessages;
   }

   public function getFormErrors(): array
   {
      return $this->formErrors;
   }

   public function setFormRules(array $rules): self
   {
      $this->formRules = $rules;
      return $this;
   }

   public function setFormMessages(array $messages): self
   {
      $this->formMessages = $messages;
      return $this;
   }

   public function validateForm(array $data): bool
   {
      $validator = new Validator(
         $data,
         $this->getFormRules(),
         $this->getFormMessages()
      );

      $isValid = $validator->validate();
      $this->formErrors = $validator->errors();

      return $isValid;
   }

   public function hasFormErrors(): bool
   {
      return !empty($this->formErrors);
   }

   public function getFieldError(string $field): ?string
   {
      return $this->formErrors[$field][0] ?? null;
   }
   
}
