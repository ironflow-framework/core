<?php

declare(strict_types=1);

namespace IronFlow\Core\Event;

use IronFlow\Core\Container\ContainerInterface;
use IronFlow\Core\Event\Contracts\EventInterface;
use IronFlow\Core\Event\Contracts\ListenerInterface;
use IronFlow\Core\Event\Contracts\SubscriberInterface;
use IronFlow\Core\Event\Exceptions\EventException;
use IronFlow\Support\Facades\Str;

/**
 * Gestionnaire d'événements
 * 
 * Cette classe gère la distribution des événements dans l'application.
 * Elle permet d'enregistrer des écouteurs d'événements et de les déclencher.
 */
class EventDispatcher
{
   private static ?EventDispatcher $instance = null;
   private array $listeners = [];
   private array $wildcards = [];
   private array $sorted = [];
   private ContainerInterface $container;

   private function __construct(ContainerInterface $container)
   {
      $this->container = $container;
   }

   public static function getInstance(ContainerInterface $container): self
   {
      if (self::$instance === null) {
         self::$instance = new self($container);
      }
      return self::$instance;
   }

   public function dispatch(string|EventInterface $event, array $payload = []): array
   {
      [$event, $payload] = $this->parseEventAndPayload($event, $payload);

      $responses = [];

      foreach ($this->getListeners($event) as $listener) {
         $response = $this->callListener($listener, $event, $payload);
         if ($response !== null) {
            $responses[] = $response;
         }

         if ($event->isPropagationStopped()) {
            break;
         }
      }

      return $responses;
   }

   public function listen(string|array $events, mixed $listener): void
   {
      foreach ((array) $events as $event) {
         if (str_contains($event, '*')) {
            $this->setupWildcardListener($event, $listener);
         } else {
            $this->listeners[$event][] = $this->makeListener($listener);
            unset($this->sorted[$event]);
         }
      }
   }

   public function subscribe(string $subscriber): void
   {
      $subscriber = $this->container->make($subscriber);

      if (!$subscriber instanceof SubscriberInterface) {
         throw new EventException("Le subscriber doit implémenter SubscriberInterface");
      }

      foreach ($subscriber->getSubscribedEvents() as $event => $params) {
         if (is_string($params)) {
            $this->listen($event, [$subscriber, $params]);
         } elseif (is_array($params)) {
            foreach ($params as $method) {
               $this->listen($event, [$subscriber, $method]);
            }
         }
      }
   }

   public function forget(string $event): void
   {
      unset($this->listeners[$event], $this->sorted[$event]);
   }

   public function forgetAll(): void
   {
      $this->listeners = [];
      $this->wildcards = [];
      $this->sorted = [];
   }

   private function parseEventAndPayload(string|EventInterface $event, array $payload): array
   {
      if (is_string($event)) {
         $event = new Event($event, $payload);
      }

      return [$event, $event->getData()];
   }

   private function getListeners(EventInterface $event): array
   {
      $name = $event->getName();

      if (isset($this->sorted[$name])) {
         return $this->sorted[$name];
      }

      $listeners = $this->listeners[$name] ?? [];
      $listeners = array_merge($listeners, $this->getWildcardListeners($name));

      return $this->sorted[$name] = $this->sortListeners($listeners);
   }

   private function setupWildcardListener(string $event, mixed $listener): void
   {
      $this->wildcards[$event][] = $this->makeListener($listener);
   }

   private function getWildcardListeners(string $eventName): array
   {
      $wildcards = [];

      foreach ($this->wildcards as $key => $listeners) {
         if (Str::is($key, $eventName)) {
            $wildcards = array_merge($wildcards, $listeners);
         }
      }

      return $wildcards;
   }

   private function makeListener(mixed $listener): callable
   {
      if (is_string($listener)) {
         return $this->createClassListener($listener);
      }

      if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
         return $this->createClassListener($listener);
      }

      return $listener;
   }

   private function createClassListener(string|array $listener): callable
   {
      if (is_string($listener)) {
         return function (...$arguments) use ($listener) {
            return $this->container->make($listener)->handle(...$arguments);
         };
      }

      return function (...$arguments) use ($listener) {
         [$class, $method] = $listener;
         return $this->container->make($class)->{$method}(...$arguments);
      };
   }

   private function sortListeners(array $listeners): array
   {
      usort($listeners, function ($a, $b) {
         return $this->getListenerPriority($b) - $this->getListenerPriority($a);
      });

      return $listeners;
   }

   private function getListenerPriority(callable $listener): int
   {
      if ($listener instanceof ListenerInterface) {
         return $listener->getPriority();
      }

      return 0;
   }

   private function callListener(callable $listener, EventInterface $event, array $payload): mixed
   {
      try {
         return $listener($event, ...$payload);
      } catch (\Throwable $e) {
         throw new EventException(
            "Erreur lors de l'exécution du listener: " . $e->getMessage(),
            0,
            $e
         );
      }
   }
}
