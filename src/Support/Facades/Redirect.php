<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Http\Response;

class Redirect
{
    /**
     * Redirige vers une URL
     */
    public static function to(string $url): Response
    {
        return new Response('', 302, ['Location' => $url]);
    }

    /**
     * Redirige vers une route nommée
     */
    public static function route(string $name, array $parameters = []): Response
    {
        return static::to(route($name, $parameters));
    }

    /**
     * Redirige vers l'URL précédente
     */
    public static function back(): Response
    {
        return static::to(session()->get('_previous.url', '/'));
    }

    /**
     * Redirige vers l'URL prévue ou une URL par défaut
     */
    public static function intended(string $default = '/'): Response
    {
        $intended = session()->pull('url.intended', $default);
        return static::to($intended);
    }

    /**
     * Ajoute des données à la session flash
     */
    public function withInput(?array $input = null): self
    {
        if ($input === null) {
            $input = request()->all();
        }
        
        session()->flash('_old_input', $input);
        return $this;
    }

    /**
     * Ajoute des erreurs à la session flash
     */
    public function withErrors(array $errors): self
    {
        session()->flash('errors', $errors);
        return $this;
    }
}
