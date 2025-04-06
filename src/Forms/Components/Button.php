<?php

namespace IronFlow\Forms\Components;

class Button extends Component
{
   protected string $type = 'submit';
   protected ?string $icon = null;

   public function __construct(string $text, array $options = [])
   {
      parent::__construct('', $text, $options);

      $this->type = $options['type'] ?? 'submit';
      $this->icon = $options['icon'] ?? null;
   }

   /**
    * Récuperer l'attribut name
    *
    * @return string
    */
   public function getName(): string
   {
      return $this->name;
   }

   /**
    * Rendu du composant
    *
    * @return string
    */
   public function render(): string
   {
      $buttonClasses = $this->combineClasses('button');
      
      // Ajout des classes spécifiques selon le type de bouton
      switch ($this->type) {
         case 'submit':
            $buttonClasses .= ' bg-[linear-gradient(145deg, #ff4d00 0%, #ff6b00 100%)] hover:bg-indigo-700';
            break;
         case 'reset':
            $buttonClasses .= ' bg-gray-600 hover:bg-gray-700';
            break;
         case 'button':
            $buttonClasses .= ' bg-blue-600 hover:bg-blue-700';
            break;
      }

      return "
         <button
            type='{$this->type}'
            name='{$this->name}'
            id='{$this->name}'
            class='{$buttonClasses}'
            " . $this->renderAttributes() . "
         >
            {$this->label}
         </button>";
   }

   protected function buildAttributes(array $attributes): string
   {
      return implode(' ', array_map(
         fn($key, $value) => $value === true ? $key : "$key=\"$value\"",
         array_keys($attributes),
         $attributes
      ));
   }
}
