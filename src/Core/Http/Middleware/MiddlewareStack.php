<?php 

declare(strict_types= 1);

namespace IronFlow\Core\Http\Middleware;

use IronFlow\Core\Container\Container;
use IronFlow\Core\Http\Request;
use IronFlow\Core\Http\Response;

final class MiddlewareStack
{
    public function __construct(
        private Container $container,
        private array $middleware,
        private $destination
    ) {}

    public function process(Request $request): Response
    {
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            $this->carry(),
            $this->destination
        );

        return $pipeline($request);
    }

    private function carry(): callable
    {
        return function (callable $stack, string $middleware): callable {
            return function (Request $request) use ($stack, $middleware): Response {
                $middlewareInstance = $this->container->make($middleware);
                return $middlewareInstance->handle($request, $stack);
            };
        };
    }
}