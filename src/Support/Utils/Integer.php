<?php

namespace IronFlow\Support\Utils;

/**
 * Classe utilitaire pour la manipulation d'entiers
 */
class Integer
{
   /**
    * Vérifie si une valeur est un entier ou une chaîne qui peut être convertie en entier
    *
    * @param mixed $value
    * @return bool
    */
   public static function is($value): bool
   {
      return is_int($value) || (is_string($value) && ctype_digit($value));
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
      return max($min, min($max, $value));
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
      return random_int($min, $max);
   }

   /**
    * Calcule la factorielle d'un nombre
    *
    * @param int $n
    * @return int
    * @throws \InvalidArgumentException
    */
   public static function factorial(int $n): int
   {
      if ($n < 0) {
         throw new \InvalidArgumentException('La factorielle n\'est définie que pour les nombres positifs.');
      }

      if ($n <= 1) {
         return 1;
      }

      return $n * self::factorial($n - 1);
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
      return number_format($value, 0, ',', $separator);
   }

   /**
    * Vérifie si un nombre est premier
    *
    * @param int $n
    * @return bool
    */
   public static function isPrime(int $n): bool
   {
      if ($n <= 1) {
         return false;
      }

      if ($n <= 3) {
         return true;
      }

      if ($n % 2 === 0 || $n % 3 === 0) {
         return false;
      }

      for ($i = 5; $i * $i <= $n; $i += 6) {
         if ($n % $i === 0 || $n % ($i + 2) === 0) {
            return false;
         }
      }

      return true;
   }

   /**
    * Vérifie si un nombre est pair
    *
    * @param int $number
    * @return bool
    */
   public static function isEven(int $number): bool
   {
      return $number % 2 === 0;
   }

   /**
    * Vérifie si un nombre est impair
    *
    * @param int $number
    * @return bool
    */
   public static function isOdd(int $number): bool
   {
      return $number % 2 !== 0;
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
      return $number % $divisor === 0;
   }

   /**
    * Vérifie si un nombre est divisible par 2
    *
    * @param int $number
    * @return bool
    */
   public static function isDivisibleBy2(int $number): bool
   {
      return $number % 2 === 0;
   }

   /**
    * Vérifie si un nombre est divisible par 3
    *
    * @param int $number
    * @return bool
    */
   public static function isDivisibleBy3(int $number): bool
   {
      return $number % 3 === 0;
   }

   /**
    * Vérifie si un nombre est divisible par 4
    *
    * @param int $number
    * @return bool
    */
   public static function isDivisibleBy4(int $number): bool
   {
      return $number % 4 === 0;
   }
}
