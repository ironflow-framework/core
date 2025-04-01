<?php

declare(strict_types=1);

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Str as StrUtils;

/**
 * Façade pour la manipulation de chaînes de caractères
 * 
 * @method static string title(string $string)
 * @method static string slug(string $string)
 * @method static string studly(string $string)
 * @method static string kebab(string $string)
 * @method static string snake(string $string)
 * @method static string camel(string $string)
 * @method static string escape(string $string)
 * @method static string unescape(string $string)
 * @method static bool startsWith(string $string, string|array $needles)
 * @method static bool endsWith(string $string, string|array $needles)
 * @method static bool contains(string $haystack, string|array $needles)
 * @method static string replace(string $string, string $needle, string $replacement)
 * @method static string remove(string $string, string $needle)
 * @method static string removeAll(string $string, array $needles)
 * @method static string removeAllSpaces(string $string)
 * @method static string removeAllSpacesAndTrim(string $string)
 * @method static string lower(string $string)
 * @method static string upper(string $string)
 * @method static string capitalize(string $string)
 * @method static string reverse(string $string)
 * @method static int length(string $string)
 * @method static string random(int $length = 16)
 * @method static string randomNumber(int $length = 16)
 * @method static string limit(string $string, int $limit = 100, string $end = '...')
 */
class Str
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
      return StrUtils::$method(...$arguments);
   }
}
