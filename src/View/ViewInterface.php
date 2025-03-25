<?php

declare(strict_types=1);

namespace IronFlow\View;

interface ViewInterface
{
   public function render(string $template, array $data = []): string;
   public function addGlobal(string $name, mixed $value): void;
   public function addFilter(string $name, callable $filter): void;
   public function addFunction(string $name, callable $function): void;
}
