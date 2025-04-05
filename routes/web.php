<?php

declare(strict_types=1);

use App\Controllers\CategoryController;
use App\Controllers\OrderController;
use App\Controllers\ProductController;
use App\Controllers\DashboardController;
use App\Controllers\WelcomeController;
use App\Controllers\PostController;
use App\Controllers\CommentController;
use IronFlow\Support\Facades\Route;

// Route d'exemple
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Routes pour l'authentification
Route::auth();

// Routes pour le dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])
   ->middleware('auth')
   ->name('dashboard');

// Routes pour les articles
Route::resource('posts', PostController::class);
Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);

// Routes pour les commandes
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('orders.update.status');
Route::delete('/orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

// Groupes de routes
// Route::group('profile', function () {
//    Route::get('/', [AuthController::class, 'profile'])->name('profile');
//    Route::post('/update', [AuthController::class, 'updateProfile'])->name('profile.update');
// // }, ['middleware' => ['auth']]);
// });
