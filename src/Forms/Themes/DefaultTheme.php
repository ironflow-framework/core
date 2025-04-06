<?php

namespace IronFlow\Forms\Themes;

use IronFlow\Forms\Form;

class DefaultTheme implements ThemeInterface
{
   public function render(Form $form): string
   {
      $html = '<form method="' . $form->getMethod() . '" action="' . $form->getAction() . '" class="form default max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg space-y-6 transition-colors duration-200">';

      if ($form->hasTitle() && $form->hasIcon()) {
         $html .= '<h1 class="text-3xl font-bold mb-6 text-[#ff4d00]><span class="' . $form->getIcon() . '"></span>' . $form->getTitle() . '</h1>';
      }

      // Groupe pour les champs normaux
      $html .= '<div class="space-y-6">';
      foreach ($form->getFields() as $field) {
         if (!($field instanceof \IronFlow\Forms\Components\Button)) {
            $value = $form->getData()[$field->getName()] ?? null;
            $field->setValue($value);

            if (isset($form->getData()['errors'][$field->getName()])) {
               $field->setError($form->getData()['errors'][$field->getName()]);
            }

            $html .= $field->render();
         }
      }
      $html .= '</div>';

      // Groupe pour les boutons avec un style spécifique
      $buttons = [];
      foreach ($form->getFields() as $field) {
         if ($field instanceof \IronFlow\Forms\Components\Button) {
            $buttons[] = $field->render();
         }
      }

      if (!empty($buttons)) {
         $html .= '<div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200 dark:border-gray-700">';
         $html .= implode('', $buttons);
         $html .= '</div>';
      }

      $html .= '</form>';

      return $html;
   }
}
