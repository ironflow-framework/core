<?php

declare(strict_types= 1);

namespace IronFlow\Core\Config\Concerns;

interface ConfigInterface
{
    /**
     * Get a configuration value by key.
     *
     * @param string $key The configuration key.
     * @param mixed $default The default value if the key does not exist.
     * @return mixed The configuration value or default.
     */
    public function get(string $key, $default = null);
}