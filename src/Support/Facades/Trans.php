<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Translator;

/**
 * Façade pour le système de traduction
 * 
 * @method static string trans(string $key, array $parameters = [], ?string $domain = null, ?string $locale = null)
 * @method static void setLocale(string $locale)
 * @method static string getLocale()
 * @method static string getDefaultLocale()
 * @method static bool has(string $key, ?string $domain = null, ?string $locale = null)
 */
class Trans
{
   /**
    * Gère les appels statiques et les redirige vers la classe utilitaire
    *
    * @param string $method
    * @param array $arguments
    * @return mixed
    */
   public static function __callStatic(string $method, array $arguments)
   {
      return Translator::$method(...$arguments);
   }

   /**
    * Traduit un message
    *
    * @param string $key
    * @param array $parameters
    * @param string|null $domain
    * @param string|null $locale
    * @return string
    */
   public static function trans(string $key, array $parameters = [], ?string $domain = null, ?string $locale = null): string
   {
      return Translator::trans($key, $parameters, $domain, $locale);
   }

   /**
    * Alias pour la méthode trans
    *
    * @param string $key
    * @param array $parameters
    * @param string|null $domain
    * @param string|null $locale
    * @return string
    */
   public static function __(string $key, array $parameters = [], ?string $domain = null, ?string $locale = null): string
   {
      return self::trans($key, $parameters, $domain, $locale);
   }

   /**
    * Change la locale actuelle
    *
    * @param string $locale
    * @return void
    */
   public static function setLocale(string $locale): void
   {
      Translator::setLocale($locale);
   }

   /**
    * Récupère la locale actuelle
    *
    * @return string
    */
   public static function getLocale(): string
   {
      return Translator::getLocale();
   }

   /**
    * Récupère la locale par défaut
    *
    * @return string
    */
   public static function getDefaultLocale(): string
   {
      return Translator::getDefaultLocale();
   }

   /**
    * Vérifie si une traduction existe
    *
    * @param string $key
    * @param string|null $domain
    * @param string|null $locale
    * @return bool
    */
   public static function has(string $key, ?string $domain = null, ?string $locale = null): bool
   {
      return Translator::has($key, $domain, $locale);
   }
}
