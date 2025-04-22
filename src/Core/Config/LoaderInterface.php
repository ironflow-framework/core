<?php

declare(strict_types=1);

namespace IronFlow\Core\Config;

/**
 * Interface pour les chargeurs de configuration
 */
interface LoaderInterface
{
   /**
    * Charge un fichier de configuration
    *
    * @param string $file Chemin du fichier à charger
    * @return array Les données de configuration
    */
   public function load(string $file): array;
}
