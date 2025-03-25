<?php

declare(strict_types=1);

use IronFlow\Routing\Router;
use App\Controllers\AuthController;
use App\Controllers\ProductController;
use App\Controllers\DashboardController;
use App\Controllers\WelcomeController;
use IronFlow\Application\Application;
use IronFlow\Http\Response;

// Route d'exemple
Router::get('/', [WelcomeController::class, 'index'])->name('home');

// Routes pour l'authentification
Router::auth();

// Routes pour le dashboard
Router::get('/dashboard', [DashboardController::class, 'index'])
   ->middleware('auth')
   ->name('dashboard');

// Routes resource
Router::resource('/products', ProductController::class);

// Groupes de routes
Router::group(['middleware' => ['auth']], function () {
   Router::get('/profile', [AuthController::class, 'profile'])->name('profile');
   Router::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});
