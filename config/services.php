<?php

declare(strict_types=1);

return [
   'providers' => [
      // Providers système
      IronFlow\Providers\AppServiceProvider::class,
      IronFlow\Providers\RouteServiceProvider::class,
      IronFlow\Providers\DatabaseServiceProvider::class,
      IronFlow\Providers\ViewServiceProvider::class,
      IronFlow\Providers\CacheServiceProvider::class,
      IronFlow\Providers\TranslationServiceProvider::class,

      // Providers de fonctionnalités
      IronFlow\Channel\ChannelServiceProvider::class,
      IronFlow\Services\AI\AIServiceProvider::class,
      IronFlow\Services\Payment\PaymentServiceProvider::class,
   ],
];
