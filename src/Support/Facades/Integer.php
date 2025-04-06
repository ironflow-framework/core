<?php

namespace IronFlow\Support\Facades;

use IronFlow\Support\Utils\Integer as IntegerUtils;

/**
 * Façade pour la manipulation d'entiers
 * 
 * @method static bool is($value)
 * @method static int between(int $value, int $min, int $max)
 * @method static int random(int $min, int $max)
 * @method static int factorial(int $n)
 * @method static string format(int $value, string $separator = ' ')
 * @method static bool isPrime(int $n)
 * @method static bool isEven(int $number)
 * @method static bool isOdd(int $number)
 * @method static bool isDivisibleBy(int $number, int $divisor)
 * @method static bool isDivisibleBy2(int $number)
 * @method static bool isDivisibleBy3(int $number)
 * @method static bool isDivisibleBy4(int $number)
 */
class Integer extends Facade
{
   protected static function getFacadeAccessor(): string
   {
      return IntegerUtils::class;
   }
}
