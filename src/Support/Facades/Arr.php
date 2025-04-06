<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Arr as ArrUtils;

/**
 * Façade pour la manipulation de tableaux
 * 
 * @method static array wrap($value)
 * @method static bool isAssoc(array $array)
 * @method static array flatten(array $array, string $prepend = '', string $delimiter = '.')
 * @method static mixed get(array $array, string|int|null $key, mixed $default = null)
 * @method static bool has(array $array, string|int $key)
 * @method static array set(array &$array, string|int $key, mixed $value)
 */
class Arr extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return ArrUtils::class;
   }
}
