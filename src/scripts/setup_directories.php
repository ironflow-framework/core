<?php

declare(strict_types=1);

// Définir le chemin de base du projet (celui qui utilise le framework)
$basePath = dirname(__DIR__, 3); // Remonte de 3 niveaux depuis vendor/ironflow/framework/scripts

$directories = [
    $basePath . '/database/migrations',
    $basePath . '/database/seeders',
    $basePath . '/database/factories',
];

// Créer les dossiers s'ils n'existent pas
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✅ Dossier créé : {$dir}\n";
    } else {
        echo "ℹ️ Dossier déjà existant : {$dir}\n";
    }
}
