<?php

use IronFlow\Support\Facades\View;
use IronFlow\Http\Response;

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        if ($message) {
            echo $message;
        }
        exit;
    }
}

if (!function_exists('view')) {
    function view(string $template, array $data = []): string
    {
        return View::render($template, $data);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): Response
    {
        return new Response(null, 302, ['Location' => $path]);
    }
}

if (!function_exists('with')) {
    function with(array $session): Response
    {
        return new Response(null, 200, [], $session);
    }
}
