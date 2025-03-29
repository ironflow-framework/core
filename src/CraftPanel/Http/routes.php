<?php

use IronFlow\CraftPanel\Controllers\CraftPanelController;
use IronFlow\CraftPanel\Middleware\CraftPanelAuthMiddleware;
use IronFlow\CraftPanel\Middleware\CraftPanelThemeMiddleware;
use IronFlow\CraftPanel\Middleware\CraftPanelCsrfMiddleware;
use IronFlow\Support\Config;
use IronFlow\Routing\Router;
use App\Controllers\AuthController;

// Récupération du préfixe depuis la configuration
$prefix = Config::get('craftpanel.routes.prefix', 'craftpanel');
$middlewares = ['web', 'auth', CraftPanelAuthMiddleware::class];

// Routes du CraftPanel
Router::group(['middleware' => $middlewares], function () use ($prefix) {
   // Route pour le tableau de bord
   Router::get("/{$prefix}", [CraftPanelController::class, 'dashboard'])->name('craftpanel.dashboard');

   // Route pour le changement de thème
   Router::get("/{$prefix}/set-theme", [CraftPanelController::class, 'setTheme'])->name('craftpanel.set-theme');

   // Routes pour les paramètres
   Router::get("/{$prefix}/settings", [CraftPanelController::class, 'settings'])->name('craftpanel.settings');
   Router::post("/{$prefix}/settings/{tab}", [CraftPanelController::class, 'updateSettings'])->name('craftpanel.settings.update');

   // Routes pour la gestion des modèles
   Router::get("/{$prefix}/models/{model}", [CraftPanelController::class, 'index'])->name('craftpanel.index');
   Router::get("/{$prefix}/models/{model}/create", [CraftPanelController::class, 'create'])->name('craftpanel.create');
   Router::post("/{$prefix}/models/{model}", [CraftPanelController::class, 'store'])->name('craftpanel.store');
   Router::get("/{$prefix}/models/{model}/{id}", [CraftPanelController::class, 'show'])->name('craftpanel.show');
   Router::get("/{$prefix}/models/{model}/{id}/edit", [CraftPanelController::class, 'edit'])->name('craftpanel.edit');
   Router::put("/{$prefix}/models/{model}/{id}", [CraftPanelController::class, 'update'])->name('craftpanel.update');
   Router::delete("/{$prefix}/models/{model}/{id}", [CraftPanelController::class, 'destroy'])->name('craftpanel.destroy');

   // Route pour l'export
   Router::get("/{$prefix}/models/{model}/export/{format?}", [CraftPanelController::class, 'export'])->name('craftpanel.export');
});

// Routes d'authentification pour le CraftPanel
Router::group(['middleware' => ['web']], function () use ($prefix) {
   Router::get("/{$prefix}/auth/login", [AuthController::class, 'showLoginForm'])->name('craftpanel.auth.login');
   Router::post("/{$prefix}/auth/login", [AuthController::class, 'login'])->name('craftpanel.auth.login.post');
   Router::post("/{$prefix}/auth/logout", [AuthController::class, 'logout'])->name('craftpanel.auth.logout');
   Router::get("/{$prefix}/auth/forgot-password", [AuthController::class, 'showForgotPasswordForm'])->name('craftpanel.auth.forgot-password');
   Router::post("/{$prefix}/auth/forgot-password", [AuthController::class, 'sendResetLinkEmail'])->name('craftpanel.auth.forgot-password.post');
   Router::get("/{$prefix}/auth/reset-password/{token}", [AuthController::class, 'showResetPasswordForm'])->name('craftpanel.auth.reset-password');
   Router::post("/{$prefix}/auth/reset-password", [AuthController::class, 'resetPassword'])->name('craftpanel.auth.reset-password.post');
});
