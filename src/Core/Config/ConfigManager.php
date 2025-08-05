<?php 

declare(strict_types= 1);

namespace IronFlow\Core\Config;

use IronFlow\Core\Config\Concerns\ConfigInterface;

class ConfigManager implements ConfigInterface
{
    protected $config = [];

    public function __construct(string $basePath = '')
    {
        $configDir = glob($basePath . '/config/*.php');

        foreach ($configDir as $file) {
            $this->config = array_merge($this->config, require $file);
        }
    }

    public function get($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->config[$key] = $value;
    }

    public function has($key)
    {
        return array_key_exists($key, $this->config);
    }

    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->config[$key]);
        }
    }

    public function clear()
    {
        $this->config = [];
    }
}