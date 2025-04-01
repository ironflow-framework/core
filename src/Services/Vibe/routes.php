<?php

declare(strict_types=1);

use IronFlow\Services\Vibe\Controllers\MediaController;
use IronFlow\Support\Facades\Route;

// Routes pour le système de médias Vibe
Route::group('media', function () {
   // Interface d'administration des médias
   Route::get('/', [MediaController::class, 'index'])->name('media.index');
   Route::get('/create', [MediaController::class, 'create'])->name('media.create');
   Route::post('/', [MediaController::class, 'store'])->name('media.store');
   Route::get('/{id}', [MediaController::class, 'show'])->name('media.show');
   Route::delete('/{id}', [MediaController::class, 'destroy'])->name('media.destroy');

   // Routes pour accéder aux fichiers
   Route::get('/{id}/download', [MediaController::class, 'download'])->name('media.download');
   Route::get('/{id}/serve', [MediaController::class, 'serve'])->name('media.serve');
}, ['middleware' => ['web']]);
