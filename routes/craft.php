<?php

use IronFlow\CraftPanel\Controllers\CraftPanelController;
use IronFlow\Support\Facades\Route;

Route::group('craft', function () {
    // Dashboard
    Route::get('/', [CraftPanelController::class, 'dashboard'])->name('craft.dashboard');

    // Resource routes for registered models
    Route::get('/{model}', [CraftPanelController::class, 'index'])->name('craft.model.index');
    Route::get('/{model}/create', [CraftPanelController::class, 'create'])->name('craft.model.create');
    Route::post('/{model}', [CraftPanelController::class, 'store'])->name('craft.model.store');
    Route::get('/{model}/{id}', [CraftPanelController::class, 'show'])->name('craft.model.show');
    Route::get('/{model}/{id}/edit', [CraftPanelController::class, 'edit'])->name('craft.model.edit');
    Route::put('/{model}/{id}', [CraftPanelController::class, 'update'])->name('craft.model.update');
    Route::delete('/{model}/{id}', [CraftPanelController::class, 'destroy'])->name('craft.model.destroy');

    // Export route
    Route::get('/{model}/export', [CraftPanelController::class, 'export'])->name('craft.model.export');

    // Bulk actions
    Route::post('/{model}/bulk', [CraftPanelController::class, 'bulk'])->name('craft.model.bulk');
}, ['middleware' => ['web', 'auth', 'admin']]);


Route::getRoutes();