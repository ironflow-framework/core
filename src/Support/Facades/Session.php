<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Session\Session as SessionManager;

class Session extends Facade
{
   /**
    * Récupère l'instance de la session
    */
   protected static function getFacadeInstance(): SessionManager
   {
      return new SessionManager();
   }

   /**
    * Génère un nouveau token CSRF
    */
   public static function generateToken(): string
   {
      return static::getFacadeInstance()->generateToken();
   }

   /**
    * Récupère le token CSRF actuel
    */
   public static function getToken(): ?string
   {
      return static::getFacadeInstance()->getToken();
   }

   /**
    * Définit une valeur dans la session
    */
   public static function set(string $key, mixed $value): void
   {
      static::getFacadeInstance()->set($key, $value);
   }

   /**
    * Récupère une valeur de la session
    */
   public static function get(string $key, mixed $default = null): mixed
   {
      return static::getFacadeInstance()->get($key, $default);
   }

   /**
    * Vérifie si une clé existe dans la session
    */
   public static function has(string $key): bool
   {
      return static::getFacadeInstance()->has($key);
   }

   /**
    * Supprime une valeur de la session
    */
   public static function remove(string $key): void
   {
      static::getFacadeInstance()->remove($key);
   }

   /**
    * Vide la session
    */
   public static function clear(): void
   {
      static::getFacadeInstance()->clear();
   }

   /**
    * Vérifie si la session est active
    */
   public static function isStarted(): bool
   {
      return static::getFacadeInstance()->isStarted();
   }

   /**
    * Récupère l'ID de la session
    */
   public static function getId(): string
   {
      return static::getFacadeInstance()->getId();
   }

   /**
    * Régénère l'ID de la session
    */
   public static function regenerate(bool $deleteOldSession = true): bool
   {
      return static::getFacadeInstance()->regenerate($deleteOldSession);
   }

   /**
    * Détruit la session
    */
   public static function destroy(): bool
   {
      return static::getFacadeInstance()->destroy();
   }
}
