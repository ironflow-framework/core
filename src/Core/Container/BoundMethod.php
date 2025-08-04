<?php

declare(strict_types=1);

namespace IronFlow\Core\Container;

use IronFlow\Core\Exception\Container\ContainerException;
use ReflectionMethod;

class BoundMethod
{
    public static function call(Container $container, callable|array|string $callback, array $parameters = []): mixed
    {
        if (static::isCallableWithAtSign($callback) || static::isStaticCallable($callback)) {
            return static::callBoundMethod($container, $callback, $parameters);
        }

        return static::callBoundMethod($container, $callback, $parameters);
    }

    protected static function callBoundMethod(Container $container, callable|array|string $callback, array $parameters): mixed
    {
        $callback = static::normalizeMethod($callback);

        if (is_array($callback)) {
            [$class, $method] = $callback;
            
            if (is_string($class)) {
                $class = $container->make($class);
            }

            $reflector = new ReflectionMethod($class, $method);
            $dependencies = static::getCallDependencies($container, $reflector->getParameters(), $parameters);

            return $reflector->invokeArgs($class, $dependencies);
        }

        return $callback(...array_values($parameters));
    }

    protected static function normalizeMethod(callable|array|string $callback): callable|array
    {
        if (is_string($callback) && str_contains($callback, '@')) {
            return explode('@', $callback, 2);
        }

        if (is_string($callback) && str_contains($callback, '::')) {
            return explode('::', $callback, 2);
        }

        return $callback;
    }

    protected static function getCallDependencies(Container $container, array $parameters, array $primitives = []): array
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $name = $parameter->getName();

            if (array_key_exists($name, $primitives)) {
                $dependencies[] = $primitives[$name];
                continue;
            }

            $type = $parameter->getType();
            
            if ($type && !$type->isBuiltin()) {
                $dependencies[] = $container->make($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            throw new ContainerException("Cannot resolve parameter [{$name}]");
        }

        return $dependencies;
    }

    protected static function isCallableWithAtSign(mixed $callback): bool
    {
        return is_string($callback) && str_contains($callback, '@');
    }

    protected static function isStaticCallable(mixed $callback): bool
    {
        return is_string($callback) && str_contains($callback, '::');
    }
}