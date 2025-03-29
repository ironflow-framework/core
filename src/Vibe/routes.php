<?php

declare(strict_types=1);

use IronFlow\Routing\Router;
use IronFlow\Vibe\Controllers\MediaController;

// Routes pour le système de médias Vibe
Router::group(['prefix' => 'media', 'middleware' => ['web']], function () {
   // Interface d'administration des médias
   Router::get('/', [MediaController::class, 'index'])->name('media.index');
   Router::get('/create', [MediaController::class, 'create'])->name('media.create');
   Router::post('/', [MediaController::class, 'store'])->name('media.store');
   Router::get('/{id}', [MediaController::class, 'show'])->name('media.show');
   Router::delete('/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

   // Routes pour accéder aux fichiers
   Router::get('/{id}/download', [MediaController::class, 'download'])->name('media.download');
   Router::get('/{id}/serve', [MediaController::class, 'serve'])->name('media.serve');
});
