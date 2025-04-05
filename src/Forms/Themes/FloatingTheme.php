<?php

namespace IronFlow\Forms\Themes;

use IronFlow\Forms\Form;

class FloatingTheme implements ThemeInterface
{
   public function render(Form $form): string
   {
      $html = '<form method="' . $form->getMethod() . '" action="' . $form->getAction() . '" class="form floating max-w-2xl mx-auto bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg space-y-6 transition-colors duration-200">';

      // Groupe pour les champs normaux avec style flottant
      $html .= '<div class="space-y-6">';
      foreach ($form->getFields() as $field) {
         if (!($field instanceof \IronFlow\Forms\Components\Button)) {
            $value = $form->getData()[$field->getName()] ?? null;
            $field->setValue($value);

            if (isset($form->getData()['errors'][$field->getName()])) {
               $field->setError($form->getData()['errors'][$field->getName()]);
            }

            // Ajout de la classe pour le style flottant
            if (method_exists($field, 'attribute')) {
               $field->attribute('class', 'peer placeholder-transparent');
               $labelClass = 'absolute left-2 -top-2.5 bg-white dark:bg-gray-800 px-2 text-sm transition-all 
                           peer-placeholder-shown:text-base peer-placeholder-shown:top-3.5 
                           peer-placeholder-shown:text-gray-400 peer-focus:-top-2.5 peer-focus:text-sm 
                           peer-focus:text-indigo-600 dark:peer-focus:text-indigo-400';
               $field->attribute('label-class', $labelClass);
            }

            // Wrap each field in a relative container for floating label
            $html .= '<div class="relative">' . $field->render() . '</div>';
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
