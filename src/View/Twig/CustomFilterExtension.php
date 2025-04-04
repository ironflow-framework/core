<?php

namespace IronFlow\View\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CustomFilterExtension extends AbstractExtension
{
   public function getFilters()
   {
      return [
         new TwigFilter('format_currency', [$this, 'formatCurrency']),
         new TwigFilter('truncate', [$this, 'truncateString']),
         new TwigFilter('slugify', [$this, 'slugify']),
      ];
   }

   public function formatCurrency(float $value, string $currency = 'EUR', int $decimals = 2): string
   {
      $formattedValue = number_format($value, $decimals, ',', ' ');

      // Si la devise est EUR, afficher le symbole €
      if (strtoupper($currency) === 'EUR') {
         return $formattedValue . " €";
      }

      return $formattedValue . " " . strtoupper($currency);
   }

   public function truncateString(string $value, int $limit = 50, string $suffix = '...'): string
   {
      return mb_strlen($value) > $limit ? mb_substr($value, 0, $limit) . $suffix : $value;
   }

   public function slugify(string $value): string
   {
      $value = strtolower(trim($value));
      $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
      return trim($value, '-');
   }

  
}
