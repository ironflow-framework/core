<?php

declare(strict_types=1);

namespace IronFlow\Components;

use IronFlow\View\TwigView;

abstract class BaseComponent
{
    protected array $props = [];
    protected TwigView $view;

    public function __construct(array $props = [])
    {
        $this->props = $props;
        $this->view = TwigView::getInstance();
    }

    abstract public function render(): string;

    protected function view(string $template, array $data = []): string
    {
        return $this->view->render("templates/{$template}", array_merge($this->props, $data));
    }
}
