<?php

declare(strict_types=1);

namespace IronFlow\Core\Config\Loaders;

use IronFlow\Core\Config\LoaderInterface;

/**
 * Chargeur de configuration pour les fichiers JSON
 */
class JsonLoader implements LoaderInterface
{
   /**
    * Charge un fichier de configuration JSON
    *
    * @param string $file Chemin du fichier à charger
    * @return array Les données de configuration
    */
   public function load(string $file): array
   {
      if (!file_exists($file)) {
         return [];
      }

      $content = file_get_contents($file);
      $config = json_decode($content, true);

      return is_array($config) ? $config : [];
   }
}
