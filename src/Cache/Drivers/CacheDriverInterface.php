<?php

declare(strict_types=1);

namespace IronFlow\Cache\Drivers;

interface CacheDriverInterface
{
    public function get(string $key): ?array;
    
    public function set(string $key, array $value): bool;
    
    public function delete(string $key): bool;
    
    public function flush(): bool;
}
