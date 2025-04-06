<?php

declare(strict_types=1);

use IronFlow\Channel\Controllers\ChannelController;
use IronFlow\Support\Facades\Route;

// Routes pour le système de channel
Route::group(['prefix' => 'broadcasting', 'middleware' => ['web']], function () {
   // Route d'authentification
   Route::post('/auth', [ChannelController::class, 'auth'])->name('channel.auth');

   // Routes pour la gestion des abonnements
   Route::post('/subscribe', [ChannelController::class, 'subscribe'])->name('channel.subscribe');
   Route::post('/unsubscribe', [ChannelController::class, 'unsubscribe'])->name('channel.unsubscribe');

   // Route pour la diffusion d'événements
   Route::post('/broadcast', [ChannelController::class, 'broadcast'])->name('channel.broadcast')->middleware(['auth']);
});
