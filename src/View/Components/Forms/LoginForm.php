<?php

namespace IronFlow\View\Components\Forms;

use IronFlow\Forms\Form;

class LoginForm extends Form
{
   public function __construct()
   {
      // parent::__construct();

      $this->addField('email', 'email', [
         'label' => 'Email',
         'required' => true,
         'placeholder' => 'Entrez votre email'
      ]);

      $this->addField('password', 'password', [
         'label' => 'Mot de passe',
         'required' => true,
         'placeholder' => 'Entrez votre mot de passe'
      ]);

      $this->addField('remember', 'checkbox', [
         'label' => 'Se souvenir de moi',
         'value' => '1'
      ]);
   }
}
