<?php

declare(strict_types=1);

use IronFlow\Support\Facades\Route;

Route::get('/api/health', function () {
   return ['status' => 'ok'];
});


