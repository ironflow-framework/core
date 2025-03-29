<?php

declare(strict_types=1);

use IronFlow\Channel\Controllers\ChannelController;
use IronFlow\Routing\Router;

// Routes pour le système de channel
Router::group(['prefix' => 'broadcasting', 'middleware' => ['web']], function () {
   // Route d'authentification
   Router::post('/auth', [ChannelController::class, 'auth'])->name('channel.auth');

   // Routes pour la gestion des abonnements
   Router::post('/subscribe', [ChannelController::class, 'subscribe'])->name('channel.subscribe');
   Router::post('/unsubscribe', [ChannelController::class, 'unsubscribe'])->name('channel.unsubscribe');

   // Route pour la diffusion d'événements
   Router::post('/broadcast', [ChannelController::class, 'broadcast'])->name('channel.broadcast')->middleware(['auth']);
});
