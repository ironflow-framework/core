<?php

declare(strict_types=1);

namespace IronFlow\Support;

class Str
{
   public static function slug(string $string): string
   {
      return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
   }

   public static function random(int $length = 16): string
   {
      return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int) ceil($length / strlen($x)))), 1, $length);
   }

   public static function randomNumber(int $length = 16): string
   {
      return substr(str_shuffle(str_repeat($x = '0123456789', (int) ceil($length / strlen($x)))), 1, $length);
   }

}
