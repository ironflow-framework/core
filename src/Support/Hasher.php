<?php

namespace IronFlow\Support;

class Hasher
{
   public static function hash(string $value): string
   {
      return hash('sha256', $value);
   }

   public static function verify(string $value, string $hash): bool
   {
      return hash_equals($hash, self::hash($value));
   }
}