<?php

declare(strict_types=1);

namespace IronFlow\Furnace\Traits;

use IronFlow\Furnace\Form;
use IronFlow\Database\Model;
use IronFlow\Furnace\ModelForm;
use IronFlow\Validation\Validator;
use ReflectionClass;

trait HasForm
{
    protected ?Form $form = null;
    protected array $formRules = [];
    protected array $formMessages = [];
    protected array $formErrors = [];

    public static function form(): ModelForm
    {
        return new ModelForm(new static());
    }

    public function getForm(): Form
    {
        if ($this->form === null) {
            $this->form = $this->createForm();
        }
        return $this->form;
    }

    protected function createForm(): Form
    {
        if (!$this instanceof Model) {
            throw new \RuntimeException("HasForm trait can only be used with Model classes");
        }

        $formClass = $this->getFormClass();
        $form = new $formClass();

        // Remplir le formulaire avec les données du modèle
        if ($this->exists) {
            $form->fill($this->toArray());
        }

        return $form;
    }

    protected function getFormClass(): string
    {
        $modelClass = (new ReflectionClass($this))->getShortName();
        $formClass = "App\\Forms\\{$modelClass}Form";

        if (!class_exists($formClass)) {
            throw new \RuntimeException("Form class {$formClass} does not exist");
        }

        return $formClass;
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
