<?php

namespace IronFlow\Support\Utils;

class Integer
{

   public static function isEven(int $number): bool
   {
      return $number % 2 === 0;
   }
   
   public static function isOdd(int $number): bool
   {
      return $number % 2 !== 0;
   }

   public static function isPrime(int $number): bool
   {
      if ($number <= 1) {
         return false;
      }
      if ($number <= 3) {
         return true;
      }
      if ($number % 2 === 0 || $number % 3 === 0) {
         return false;
      }
      for ($i = 5; $i * $i <= $number; $i += 6) {
         if ($number % $i === 0 || $number % ($i + 2) === 0) {
            return false;
         }
      }
      return true;
   }

   public static function isDivisibleBy(int $number, int $divisor): bool
   {
      return $number % $divisor === 0;
   }

   public static function isDivisibleBy2(int $number): bool
   {
      return $number % 2 === 0;
   }

   public static function isDivisibleBy3(int $number): bool 
   {
      return $number % 3 === 0;
   }

   public static function isDivisibleBy4(int $number): bool
   {
      return $number % 4 === 0;
   }   
   
}
