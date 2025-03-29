<?php

declare(strict_types=1);

namespace IronFlow\Furnace\Forms;

use IronFlow\Furnace\Form;
use IronFlow\Furnace\Components\Input;
use IronFlow\Furnace\Components\Textarea;

class ContactForm extends Form
{
    public function __construct()
    {
        $this->setMethod('POST')
             ->setAction(route('contact.send'));

        $this->addComponent(new Input('name', 'Your Name'))
             ->setRequired(true)
             ->setRules(['required', 'min:2']);

        $this->addComponent(new Input('email', 'Email Address'))
             ->setType('email')
             ->setRequired(true)
             ->setRules(['required', 'email']);

        $this->addComponent(new Input('subject', 'Subject'))
             ->setRequired(true)
             ->setRules(['required', 'min:5']);

        $this->addComponent(new Textarea('message', 'Message'))
             ->setRequired(true)
             ->setRules(['required', 'min:10'])
             ->setAttributes(['rows' => 5]);
    }

    public function getValidationRules(): array
    {
        return [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10'
        ];
    }

    public function getValidationMessages(): array
    {
        return [
            'name.required' => 'Please enter your name',
            'name.min' => 'Name must be at least 2 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please enter a valid email address',
            'subject.required' => 'Subject is required',
            'subject.min' => 'Subject must be at least 5 characters',
            'message.required' => 'Please enter your message',
            'message.min' => 'Message must be at least 10 characters'
        ];
    }
}
