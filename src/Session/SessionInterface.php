<?php

namespace IronFlow\Session;

interface SessionInterface
{
    public function set(string $name, mixed $value);
    public function get(string $name, mixed $default = null): mixed;
    public function remove(string $name): mixed;
    public function clear();
    public function has(string $name): bool;
    public function destroy();
    public function clearExpired();
}