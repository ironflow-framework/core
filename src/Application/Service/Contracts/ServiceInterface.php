<?php

namespace IronFlow\Support\Service\Contracts;

interface ServiceInterface
{
   public function register(): void;
   public function boot(): void;
}