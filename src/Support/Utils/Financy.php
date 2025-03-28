<?php

namespace IronFlow\Support\Utils;

class Financy
{

   public static function calculateInterest(float $principal, float $rate, float $time): float
   {
      return $principal * $rate * $time;
   }

   public static function calculateCompoundInterest(float $principal, float $rate, float $time, float $compoundFrequency): float
   {
      return $principal * pow(1 + $rate / $compoundFrequency, $compoundFrequency * $time);
   }

   public static function calculatePresentValue(float $futureValue, float $rate, float $time): float
   {
      return $futureValue / pow(1 + $rate, $time);
   }

   public static function calculateFutureValue(float $presentValue, float $rate, float $time): float
   {
      return $presentValue * pow(1 + $rate, $time);
   }

   public static function calculateAnnuity(float $payment, float $rate, float $time): float
   {
      return $payment * (pow(1 + $rate, $time) - 1) / $rate;
   }

   public static function calculatePresentValueAnnuity(float $payment, float $rate, float $time): float
   {
      return $payment * (1 - pow(1 + $rate, -$time)) / $rate;
   }

   public static function calculateTVA(float $price, float $tva): float
   {
      return $price * (1 + $tva);
   }
   
   public static function calculateTTC(float $price, float $tva): float
   {
      return $price * (1 + $tva);
   }
   
   
}
