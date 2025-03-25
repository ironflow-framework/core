<?php

namespace IronFlow\View\Components\Forms;

use IronFlow\Forms\Form;

class ContactForm extends Form
{
   public function __construct()
   {
      // parent::__construct();

      $this->addField('name', 'text', [
         'label' => 'Nom',
         'required' => true,
         'placeholder' => 'Votre nom'
      ]);

      $this->addField('email', 'email', [
         'label' => 'Email',
         'required' => true,
         'placeholder' => 'Votre email'
      ]);

      $this->addField('subject', 'text', [
         'label' => 'Sujet',
         'required' => true,
         'placeholder' => 'Le sujet de votre message'
      ]);

      $this->addField('message', 'textarea', [
         'label' => 'Message',
         'required' => true,
         'placeholder' => 'Votre message',
         'rows' => 5
      ]);
   }
}
