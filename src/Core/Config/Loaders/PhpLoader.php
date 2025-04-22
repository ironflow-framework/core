<?php

declare(strict_types=1);

namespace IronFlow\Core\Config\Loaders;

use IronFlow\Core\Config\LoaderInterface;

/**
 * Chargeur de configuration pour les fichiers PHP
 */
class PhpLoader implements LoaderInterface
{
   /**
    * Charge un fichier de configuration PHP
    *
    * @param string $file Chemin du fichier à charger
    * @return array Les données de configuration
    */
   public function load(string $file): array
   {
      if (!file_exists($file)) {
         return [];
      }

      $config = require $file;
      return is_array($config) ? $config : [];
   }
}
