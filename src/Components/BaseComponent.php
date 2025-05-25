<?php

declare(strict_types=1);

namespace IronFlow\Components;

abstract class BaseComponent
{
   abstract public function render(): string;
}
