<?php

declare(strict_types=1);

namespace IronFlow\Support\Utils;

class Str
{

   public static function title(string $string): string
   {
      return ucwords(str_replace('_', ' ', $string));
   }

   public static function slug(string $string): string
   {
      return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
   }

   public static function escape(string $string): string
   {
      return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
   }

   public static function unescape(string $string): string
   {
      return htmlspecialchars_decode($string, ENT_QUOTES);
   }

   public static function startsWith(string $string, string $needle): bool
   {
      return strpos($string, $needle) === 0;
   }

   public static function endsWith(string $string, string $needle): bool
   {
      return strrpos($string, $needle) === strlen($string) - strlen($needle);
   }

   public static function contains(string $string, string $needle): bool
   {
      return strpos($string, $needle) !== false;
   }

   public static function replace(string $string, string $needle, string $replacement): string
   {
      return str_replace($needle, $replacement, $string);
   }

   public static function remove(string $string, string $needle): string
   {
      return str_replace($needle, '', $string);
   }

   public static function removeAll(string $string, array $needles): string
   {
      return str_replace($needles, '', $string);
   }

   public static function removeAllSpaces(string $string): string
   {
      return preg_replace('/\s+/', '', $string);
   }

   public static function removeAllSpacesAndTrim(string $string): string
   {
      return trim(preg_replace('/\s+/', '', $string));
   }

   public static function removeAllSpacesAndTrimAndLower(string $string): string
   {
      return strtolower(trim(preg_replace('/\s+/', '', $string)));
   }

   public static function removeAllSpacesAndTrimAndLowerAndReplace(string $string, string $needle, string $replacement): string
   {
      return strtolower(trim(str_replace($needle, $replacement, preg_replace('/\s+/', '', $string))));
   }
   
   public static function lower(string $string): string
   {
      return strtolower($string);
   }

   public static function upper(string $string): string
   {
      return strtoupper($string);
   }

   public static function capitalize(string $string): string
   {
      return ucwords($string);
   }

   public static function reverse(string $string): string
   {
      return strrev($string);
   }

   public static function length(string $string): int
   {
      return strlen($string);
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
