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
class Trans extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return Translator::class;
   }
}
