<?php

declare(strict_types=1);

namespace IronFlow\Session;

use Symfony\Component\HttpFoundation\Session\Session as BaseSession;

class Session extends BaseSession
{
    public function __construct()
    {
        parent::__construct();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return parent::get($name, $default);
    }

    public function put(string $key, $value): void
    {
        $this->set($key, $value);
    }

    public function pull(string $key, $default = null)
    {
        $value = $this->get($key, $default);
        $this->remove($key);
        return $value;
    }

    public function flash(string $key, $value): void
    {
        $this->getFlashBag()->add($key, $value);
    }

    public function reflash(?array $keys = null): void
    {
        $flash = $this->getFlashBag()->peekAll();
        if ($keys === null) {
            $keys = array_keys($flash);
        }
        foreach ($keys as $key) {
            if (isset($flash[$key])) {
                foreach ($flash[$key] as $value) {
                    $this->flash($key, $value);
                }
            }
        }
    }

    public function now(string $key, $value): void
    {
        $this->put($key, $value);
        $this->flash('_now', [$key => $value]);
    }

    public function keep(?array $keys = null): void
    {
        $this->reflash($keys);
    }

    public function invalidate(?int $lifetime = null): bool
    {
        $this->clear();
        return parent::invalidate($lifetime);
    }

    public function all(): array
    {
        return parent::all();
    }

    public function regenerate()
    {
        $this->clear();
        session_regenerate_id();
        return true;
    }
}
