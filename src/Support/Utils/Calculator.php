<?php

namespace IronFlow\Support\Utils;


class Calculator extends Integer
{

   public static function add(int $a, int $b): int
   {
      return $a + $b;
   }

   public static function subtract(int $a, int $b): int
   {
      return $a - $b;
   }

   public static function multiply(int $a, int $b): int
   {
      return $a * $b;
   }

   public static function divide(int $a, int $b): int
   {
      return $a / $b;
   }

   public static function modulus(int $a, int $b): int
   {  
      return $a % $b;
   }

   public static function power(int $a, int $b): int
   {
      return $a ** $b;
   }

   public static function square(int $a): int
   {
      return $a * $a;
   }

   public static function cube(int $a): int
   {
      return $a * $a * $a;
   }

   public static function squareRoot(int $a): int
   {
      return sqrt($a);
   }

   public static function cubeRoot(int $a): int
   {
      return pow($a, 1/3);
   }

   public static function factorial(int $a): int
   {
      return $a * ($a - 1) * ($a - 2) * ($a - 3) * ($a - 4) * ($a - 5) * ($a - 6) * ($a - 7) * ($a - 8) * ($a - 9);
   }
   
   
}
