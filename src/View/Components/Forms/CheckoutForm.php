<?php

namespace IronFlow\View\Components\Forms;

use IronFlow\Forms\Form;

class CheckoutForm extends Form
{
   public function __construct()
   {
      // parent::__construct();

      // Informations de facturation
      $this->addField('billing_name', 'text', [
         'label' => 'Nom de facturation',
         'required' => true,
         'placeholder' => 'Nom complet'
      ]);

      $this->addField('billing_email', 'email', [
         'label' => 'Email de facturation',
         'required' => true,
         'placeholder' => 'Email'
      ]);

      $this->addField('billing_address', 'textarea', [
         'label' => 'Adresse de facturation',
         'required' => true,
         'placeholder' => 'Adresse complète',
         'rows' => 3
      ]);

      // Informations de paiement
      $this->addField('card_number', 'text', [
         'label' => 'Numéro de carte',
         'required' => true,
         'placeholder' => '1234 5678 9012 3456'
      ]);

      $this->addField('card_expiry', 'text', [
         'label' => 'Date d\'expiration',
         'required' => true,
         'placeholder' => 'MM/AA'
      ]);

      $this->addField('card_cvc', 'text', [
         'label' => 'CVC',
         'required' => true,
         'placeholder' => '123'
      ]);
   }
}
