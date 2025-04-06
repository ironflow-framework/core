<?php

namespace IronFlow\Forms\Themes;

use IronFlow\Forms\Form;

class MaterialTheme implements ThemeInterface
{
   public function render(Form $form): string
   {
      $html = '<form method="' . $form->getMethod() . '" action="' . $form->getAction() . '" class="form material">';

      if ($form->hasTitle() && $form->hasIcon()) {
         $html .= '<h1 class="text-3xl font-bold mb-6 text-[#ff4d00]><span class="' . $form->getIcon() . '"></span>' . $form->getTitle() . '</h1>';
      }

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
