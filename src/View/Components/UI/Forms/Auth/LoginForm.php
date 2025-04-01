<?php

declare(strict_types=1);

namespace IronFlow\Forms\Furnace\Forms\Auth;

use IronFlow\Forms\Furnace\Components\Input;
use IronFlow\Forms\Furnace\Form;

class LoginForm extends Form
{
    public function __construct()
    {
        $this->setMethod('POST')
             ->setAction(route('login'));

        $this->addComponent(new Input('email', 'Email'))
             ->setType('email')
             ->setRequired(true)
             ->setRules(['required', 'email']);

        $this->addComponent(new Input('password', 'Password'))
             ->setType('password')
             ->setRequired(true)
             ->setRules(['required', 'min:8']);

        $this->addComponent(new Input('remember_me', 'Remember Me'))
             ->setType('checkbox');
    }

    public function getValidationRules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters'
        ];
    }
}
