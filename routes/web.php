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

// Route racine (doit être définie en premier)
Route::get('/', [WelcomeController::class, 'index'])->name('home');

// Authentication routes
Route::auth();

// Blog routes
Route::group('blog', function () {
   Route::resource('posts', PostController::class);
   Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('comments.store');
   Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
}, ['name' => 'blog.']);

// E-commerce routes
Route::group('shop', function () {
   Route::resource('products', ProductController::class);
   Route::resource('categories', CategoryController::class);

   // Orders with middleware auth
   Route::group('orders', function () {
      Route::get('/', [OrderController::class, 'index'])->name('index');
      Route::get('/create', [OrderController::class, 'create'])->name('create');
      Route::post('/store', [OrderController::class, 'store'])->name('store');
      Route::get('/{id}', [OrderController::class, 'show'])->name('show');
      Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->name('update.status');
      Route::delete('/{id}', [OrderController::class, 'destroy'])->name('destroy');
   }, ['middleware' => ['auth'], 'name' => 'orders.']);
}, ['name' => 'shop.']);

// Admin routes
Route::group('admin', function () {
   Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
}, ['middleware' => ['auth', 'admin'], 'name' => 'admin.']);
