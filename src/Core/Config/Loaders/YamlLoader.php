<?php

declare(strict_types=1);

namespace IronFlow\Core\Config\Loaders;

use IronFlow\Core\Config\LoaderInterface;

/**
 * Chargeur de configuration pour les fichiers YAML
 */
class YamlLoader implements LoaderInterface
{
   /**
    * Charge un fichier de configuration YAML
    *
    * @param string $file Chemin du fichier à charger
    * @return array Les données de configuration
    * @throws \RuntimeException Si le fichier YAML est invalide
    */
   public function load(string $file): array
   {
      if (!file_exists($file)) {
         return [];
      }

      $content = file_get_contents($file);
      if ($content === false) {
         throw new \RuntimeException('Impossible de lire le fichier YAML');
      }

      // Conversion simple de YAML en JSON puis en tableau
      $content = preg_replace('/^\s*-\s*/m', '"item":', $content); // Convertit les listes
      $content = preg_replace('/:\s*([^"\'\n]+)$/m', ': "$1"', $content); // Ajoute des guillemets aux valeurs
      $content = preg_replace('/^\s*([^"\'\s]+):/m', '"$1":', $content); // Ajoute des guillemets aux clés
      $content = '{' . $content . '}'; // Entoure le tout avec des accolades

      $config = json_decode($content, true);
      if (json_last_error() !== JSON_ERROR_NONE) {
         throw new \RuntimeException('Erreur lors du parsing du fichier YAML: ' . json_last_error_msg());
      }

      return is_array($config) ? $config : [];
   }
}
