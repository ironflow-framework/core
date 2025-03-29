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
class Integer
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
      return IntegerUtils::$method(...$arguments);
   }

   /**
    * Vérifie si une valeur est un entier ou une chaîne qui peut être convertie en entier
    *
    * @param mixed $value
    * @return bool
    */
   public static function is($value): bool
   {
      return IntegerUtils::is($value);
   }

   /**
    * Assure que la valeur est entre un minimum et un maximum
    *
    * @param int $value
    * @param int $min
    * @param int $max
    * @return int
    */
   public static function between(int $value, int $min, int $max): int
   {
      return IntegerUtils::between($value, $min, $max);
   }

   /**
    * Génère un nombre aléatoire entre min et max
    *
    * @param int $min
    * @param int $max
    * @return int
    */
   public static function random(int $min, int $max): int
   {
      return IntegerUtils::random($min, $max);
   }

   /**
    * Calcule la factorielle d'un nombre
    *
    * @param int $n
    * @return int
    */
   public static function factorial(int $n): int
   {
      return IntegerUtils::factorial($n);
   }

   /**
    * Formate un entier avec des séparateurs de milliers
    *
    * @param int $value
    * @param string $separator
    * @return string
    */
   public static function format(int $value, string $separator = ' '): string
   {
      return IntegerUtils::format($value, $separator);
   }

   /**
    * Vérifie si un nombre est premier
    *
    * @param int $n
    * @return bool
    */
   public static function isPrime(int $n): bool
   {
      return IntegerUtils::isPrime($n);
   }

   /**
    * Vérifie si un nombre est pair
    *
    * @param int $number
    * @return bool
    */
   public static function isEven(int $number): bool
   {
      return IntegerUtils::isEven($number);
   }

   /**
    * Vérifie si un nombre est impair
    *
    * @param int $number
    * @return bool
    */
   public static function isOdd(int $number): bool
   {
      return IntegerUtils::isOdd($number);
   }

   /**
    * Vérifie si un nombre est divisible par un autre
    *
    * @param int $number
    * @param int $divisor
    * @return bool
    */
   public static function isDivisibleBy(int $number, int $divisor): bool
   {
      return IntegerUtils::isDivisibleBy($number, $divisor);
   }
}
