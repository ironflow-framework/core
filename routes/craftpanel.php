<?php

use IronFlow\Support\Facades\Route;
use App\Http\Controllers\CraftPanel\DashboardController;
use App\Http\Controllers\CraftPanel\AuthController;
use App\Http\Controllers\CraftPanel\UserController;
use App\Http\Controllers\CraftPanel\RoleController;
use App\Http\Controllers\CraftPanel\SettingController;

$prefix = config('craftpanel.routes.prefix', 'craftpanel');
$middleware = config('craftpanel.routes.middleware', ['web', 'auth']);
$namespace = config('craftpanel.routes.namespace', 'App\\Http\\Controllers\\CraftPanel');

Route::prefix($prefix)
    ->middleware($middleware)
    ->namespace($namespace)
    ->group(function () {
        // Routes d'authentification
        Route::get('login', [AuthController::class, 'showLoginForm'])->name('craftpanel.login');
        Route::post('login', [AuthController::class, 'login']);
        Route::post('logout', [AuthController::class, 'logout'])->name('craftpanel.logout');

        // Routes protégées
        Route::middleware(['auth:craftpanel'])->group(function () {
            // Dashboard
            Route::get('dashboard', [DashboardController::class, 'index'])->name('craftpanel.dashboard');

            // Gestion des utilisateurs
            Route::resource('users', UserController::class);
            Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
                ->name('craftpanel.users.toggle-status');

            // Gestion des rôles
            Route::resource('roles', RoleController::class);
            Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])
                ->name('craftpanel.roles.permissions');

            // Paramètres
            Route::get('settings', [SettingController::class, 'index'])->name('craftpanel.settings');
            Route::post('settings', [SettingController::class, 'update'])->name('craftpanel.settings.update');
        });
    });
