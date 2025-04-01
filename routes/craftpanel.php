<?php

use IronFlow\Support\Facades\Route;
use App\Controllers\CraftPanel\DashboardController;
use App\Controllers\CraftPanel\AuthController;
use App\Controllers\CraftPanel\UserController;
use App\Controllers\CraftPanel\RoleController;
use App\Controllers\CraftPanel\SettingController;

$prefix = config('craftpanel.routes.prefix', 'craftpanel');
$middleware = config('craftpanel.routes.middleware', ['web', 'auth']);
$namespace = config('craftpanel.routes.namespace', 'App\\Controllers\\CraftPanel');

Route::group($prefix, function () {
    // Routes d'authentification
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('craftpanel.login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('craftpanel.logout');

    // Routes protégées
    Route::group('', function () {
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('craftpanel.dashboard');

        // Gestion des utilisateurs
        Route::resource('users', UserController::class);
        Route::post('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('craftpanel.users.toggle-status');

        // Gestion des rôles
        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('craftpanel.roles.permissions');

        // Paramètres
        Route::get('settings', [SettingController::class, 'index'])->name('craftpanel.settings');
        Route::post('settings', [SettingController::class, 'update'])->name('craftpanel.settings.update');
    })
    ->middleware(['auth:craftpanel']);
});
// }, ['middleware' => $middleware, 'namespace' => $namespace]);
