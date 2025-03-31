<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Components;

abstract class FormComponent
{
    protected string $name;
    protected string $label;
    protected mixed $value;
    protected array $attributes = [];
    protected array $rules = [];
    protected array $errors = [];

    public function __construct(string $name, string $label = '')
    {
        $this->name = $name;
        $this->label = $label ?: ucfirst(str_replace('_', ' ', $name));
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function setRules(array $rules): self
    {
        $this->rules = $rules;
        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    public function setErrors(array $errors): self
    {
        $this->errors = $errors;
        return $this;
    }

    protected function renderAttributes(): string
    {
        $html = '';
        foreach ($this->attributes as $key => $value) {
            $html .= sprintf(' %s="%s"', $key, htmlspecialchars($value));
        }
        return $html;
    }

    protected function renderLabel(): string
    {
        return sprintf(
            '<label for="%s" class="block text-sm font-medium text-gray-700">%s</label>',
            $this->name,
            $this->label
        );
    }

    protected function renderErrors(): string
    {
        if (empty($this->errors)) {
            return '';
        }

        $html = '<div class="mt-1">';
        foreach ($this->errors as $error) {
            $html .= sprintf(
                '<p class="text-sm text-red-600">%s</p>',
                htmlspecialchars($error)
            );
        }
        $html .= '</div>';

        return $html;
    }

    abstract public function render(): string;
}
