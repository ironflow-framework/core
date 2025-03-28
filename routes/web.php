<?php

declare(strict_types=1);

use IronFlow\Routing\Router;
use App\Controllers\AuthController;
use App\Controllers\OrderController;
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

// Routes resource pour les produits
Router::resource('/products', ProductController::class);

// Routes pour les commandes
Router::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Router::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Router::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
Router::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
Router::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update.status');
Router::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

// Groupes de routes
Router::group(['middleware' => ['auth']], function () {
   Router::get('/profile', [AuthController::class, 'profile'])->name('profile');
   Router::post('/profile/update', [AuthController::class, 'updateProfile'])->name('profile.update');
});
