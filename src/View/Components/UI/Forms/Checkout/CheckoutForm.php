<?php

declare(strict_types=1);

namespace IronFlow\View\Components\UI\Forms\Checkout;


use IronFlow\Forms\Furnace\Components\Input;
use IronFlow\Forms\Furnace\Components\Select;
use IronFlow\Forms\Furnace\Form;

class CheckoutForm extends Form
{
    public function __construct()
    {
        $this->setMethod('POST')
             ->setAction(route('checkout.process'));

        // Billing Information
        $this->addComponent(new Input('billing_name', 'Full Name'))
             ->setRequired(true)
             ->setRules(['required', 'min:2']);

        $this->addComponent(new Input('billing_email', 'Email Address'))
             ->setType('email')
             ->setRequired(true)
             ->setRules(['required', 'email']);

        $this->addComponent(new Input('billing_phone', 'Phone Number'))
             ->setType('tel')
             ->setRequired(true)
             ->setRules(['required', 'regex:/^[0-9\-\+\s\(\)]+$/']);

        $this->addComponent(new Input('billing_address', 'Street Address'))
             ->setRequired(true)
             ->setRules(['required']);

        $this->addComponent(new Input('billing_city', 'City'))
             ->setRequired(true)
             ->setRules(['required']);

        $this->addComponent(new Input('billing_country', 'Country'))
             ->setRequired(true)
             ->setRules(['required'])
             ->setAttributes(['autocomplete' => 'country']);

        $this->addComponent(new Input('billing_postal_code', 'Postal Code'))
             ->setRequired(true)
             ->setRules(['required', 'regex:/^[0-9A-Z\-\s]+$/i']);

        // Payment Information
        $this->addComponent(new Select('payment_method', 'Payment Method'))
             ->setOptions([
                 'credit_card' => 'Credit Card',
                 'paypal' => 'PayPal',
                 'bank_transfer' => 'Bank Transfer'
             ])
             ->setRequired(true)
             ->setRules(['required']);
             
        $this->addComponent(new Input('card_number', 'Card Number'))
             ->setType('text')
             ->setRequired(true)
             ->setRules(['required', 'regex:/^[0-9\s]{13,19}$/'])
             ->setAttributes(['autocomplete' => 'cc-number']);

        $this->addComponent(new Input('card_expiry', 'Expiry Date'))
             ->setType('text')
             ->setRequired(true)
             ->setRules(['required', 'regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/'])
             ->setAttributes(['placeholder' => 'MM/YY']);

        $this->addComponent(new Input('card_cvc', 'CVC'))
             ->setType('text')
             ->setRequired(true)
             ->setRules(['required', 'regex:/^[0-9]{3,4}$/'])
             ->setAttributes(['autocomplete' => 'cc-csc']);
    }

    public function getValidationRules(): array
    {
        return [
            'billing_name' => 'required|min:2',
            'billing_email' => 'required|email',
            'billing_phone' => 'required|regex:/^[0-9\-\+\s\(\)]+$/',
            'billing_address' => 'required',
            'billing_city' => 'required',
            'billing_postal_code' => 'required|regex:/^[0-9A-Z\-\s]+$/i',
            'card_number' => 'required|regex:/^[0-9\s]{13,19}$/',
            'card_expiry' => 'required|regex:/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
            'card_cvc' => 'required|regex:/^[0-9]{3,4}$/'
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'billing_name.required' => 'Please enter your full name',
            'billing_email.required' => 'Email address is required',
            'billing_email.email' => 'Please enter a valid email address',
            'billing_phone.required' => 'Phone number is required',
            'billing_phone.regex' => 'Please enter a valid phone number',
            'billing_address.required' => 'Street address is required',
            'billing_city.required' => 'City is required',
            'billing_postal_code.required' => 'Postal code is required',
            'billing_postal_code.regex' => 'Please enter a valid postal code',
            'card_number.required' => 'Card number is required',
            'card_number.regex' => 'Please enter a valid card number',
            'card_expiry.required' => 'Card expiry date is required',
            'card_expiry.regex' => 'Please enter a valid expiry date (MM/YY)',
            'card_cvc.required' => 'CVC is required',
            'card_cvc.regex' => 'Please enter a valid CVC'
        ];
    }
}
