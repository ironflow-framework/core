<?php

declare(strict_types=1);

namespace IronFlow\Security;

class XssProtection
{
   /**
    * Liste des balises HTML dangereuses
    */
   private const DANGEROUS_TAGS = [
      'script',
      'style',
      'iframe',
      'object',
      'embed',
      'form',
      'input',
      'textarea',
      'select',
      'option',
      'button',
      'a',
      'img',
      'video',
      'audio',
      'source',
      'track',
      'canvas',
      'svg',
      'math',
      'link',
      'meta',
      'base',
      'noscript',
      'template',
      'slot',
   ];

   /**
    * Liste des attributs HTML dangereux
    */
   private const DANGEROUS_ATTRIBUTES = [
      'onload',
      'onerror',
      'onclick',
      'onmouseover',
      'onmouseout',
      'onmouseenter',
      'onmouseleave',
      'onmouseup',
      'onmousedown',
      'onkeyup',
      'onkeydown',
      'onkeypress',
      'onfocus',
      'onblur',
      'onchange',
      'onsubmit',
      'onreset',
      'onselect',
      'oncut',
      'oncopy',
      'onpaste',
      'ondrag',
      'ondragstart',
      'ondragend',
      'ondragover',
      'ondragenter',
      'ondragleave',
      'ondrop',
      'onwheel',
      'onresize',
      'onmessage',
      'onstorage',
      'ononline',
      'onoffline',
      'onpopstate',
      'onhashchange',
      'onbeforeunload',
      'onunload',
      'onbeforeprint',
      'onafterprint',
      'onabort',
      'oncanplay',
      'oncanplaythrough',
      'oncuechange',
      'ondurationchange',
      'onemptied',
      'onended',
      'onloadeddata',
      'onloadedmetadata',
      'onloadstart',
      'onpause',
      'onplay',
      'onplaying',
      'onprogress',
      'onratechange',
      'onseeked',
      'onseeking',
      'onstalled',
      'onsuspend',
      'ontimeupdate',
      'onvolumechange',
      'onwaiting',
   ];

   /**
    * Vérifie si une chaîne contient des attaques XSS potentielles
    */
   public static function containsXss(string $value): bool
   {
      // Vérifie les balises HTML dangereuses
      foreach (self::DANGEROUS_TAGS as $tag) {
         if (preg_match('/<' . $tag . '[^>]*>/i', $value)) {
            return true;
         }
      }

      // Vérifie les attributs HTML dangereux
      foreach (self::DANGEROUS_ATTRIBUTES as $attribute) {
         if (preg_match('/' . $attribute . '=/i', $value)) {
            return true;
         }
      }

      // Vérifie les expressions JavaScript
      if (preg_match('/javascript:/i', $value)) {
         return true;
      }

      // Vérifie les expressions data
      if (preg_match('/data:/i', $value)) {
         return true;
      }

      return false;
   }

   /**
    * Nettoie une chaîne pour la rendre sûre
    */
   public static function sanitize(string $value): string
   {
      // Supprime les balises HTML dangereuses
      $value = strip_tags($value, '<p><br><strong><em><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><table><tr><td><th><thead><tbody><tfoot>');

      // Échappe les caractères spéciaux
      $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

      return $value;
   }

   /**
    * Vérifie si un tableau contient des attaques XSS potentielles
    */
   public static function containsXssInArray(array $array): bool
   {
      foreach ($array as $value) {
         if (is_string($value) && self::containsXss($value)) {
            return true;
         }
         if (is_array($value) && self::containsXssInArray($value)) {
            return true;
         }
      }

      return false;
   }

   /**
    * Nettoie un tableau pour le rendre sûr
    */
   public static function sanitizeArray(array $array): array
   {
      $result = [];

      foreach ($array as $key => $value) {
         if (is_string($value)) {
            $result[$key] = self::sanitize($value);
         } elseif (is_array($value)) {
            $result[$key] = self::sanitizeArray($value);
         } else {
            $result[$key] = $value;
         }
      }

      return $result;
   }
}
