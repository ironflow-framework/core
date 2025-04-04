<?php

namespace IronFlow\Forms\Themes;

use IronFlow\Forms\Form;

class TailwindTheme implements ThemeInterface
{
   public function render(Form $form): string
   {
      $html = '<form method="' . $form->getMethod() . '" action="' . $form->getAction() . '" class="form tailwind">';

      foreach ($form->getFields() as $field) {
         if ($field instanceof \IronFlow\Forms\Components\Button) {
            continue;
         }

         $value = $form->getData()[$field->getName()] ?? null;
         $field->setValue($value);

         if (isset($form->getData()['errors'][$field->getName()])) {
            $field->setErrors($form->getData()['errors'][$field->getName()]);
         }

         $html .= $field->render();
      }

      // Render buttons at the end
      foreach ($form->getFields() as $field) {
         if ($field instanceof \IronFlow\Forms\Components\Button) {
            $html .= $field->render();
         }
      }

      $html .= '</form>';

      return $html;
   }
}
