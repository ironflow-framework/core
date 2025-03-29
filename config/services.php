<?php

declare(strict_types=1);

return [
   // Providers système
   IronFlow\Providers\AppServiceProvider::class,
   IronFlow\Providers\RouteServiceProvider::class,
   IronFlow\Providers\DatabaseServiceProvider::class,
   IronFlow\Providers\ViewServiceProvider::class,
   IronFlow\Providers\CacheServiceProvider::class,
   IronFlow\Providers\TranslationServiceProvider::class,

   // Providers de fonctionnalités
   IronFlow\Payment\PaymentServiceProvider::class,
   IronFlow\Channel\ChannelServiceProvider::class,
   IronFlow\Framework\AI\AIServiceProvider::class,
];
