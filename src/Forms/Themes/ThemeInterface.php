<?php

namespace IronFlow\Forms\Themes;

use IronFlow\Forms\Form;

interface ThemeInterface
{
   public function render(Form $form): string;
}
