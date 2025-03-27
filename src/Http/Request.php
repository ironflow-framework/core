<?php

declare(strict_types=1);

namespace IronFlow\Http;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{
   protected array $routeParameters = [];

   public static function capture(): static
   {
      return static::createFromGlobals();
   }

   public function setRouteParameters(array $parameters): void
   {
      $this->routeParameters = $parameters;
   }

   public function getRouteParameters(): array
   {
      return $this->routeParameters;
   }

   public function getRouteParameter(string $key, mixed $default = null): mixed
   {
      return $this->routeParameters[$key] ?? $default;
   }

   public function all(): array
   {
      return array_merge($this->request->all(), $this->query->all());
   }

   public function input(string $key, mixed $default = null): mixed
   {
      return $this->request->get($key, $this->query->get($key, $default));
   }

   public function only(array $keys): array
   {
      $all = array_merge($this->request->all(), $this->query->all());
      return array_intersect_key($all, array_flip($keys));
   }

   public function except(array $keys): array
   {
      $all = array_merge($this->request->all(), $this->query->all());
      return array_diff_key($all, array_flip($keys));
   }

   public function isAjax(): bool
   {
      return $this->isXmlHttpRequest();
   }

   public function json(?string $key = null, $default = null): mixed
   {
      $data = json_decode($this->getContent(), true);

      if ($key === null) {
         return $data ?? $default;
      }

      return $data[$key] ?? $default;
   }

   public function getBaseRequest(): SymfonyRequest
   {
      return $this;
   }
}
