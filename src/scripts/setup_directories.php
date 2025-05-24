<?php

chdir(dirname(__DIR__, 3));

$directories = [
    'database/migrations',
    'database/factories',
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "Dossier créé : $dir\n";
    } else {
        echo "Le dossier existe déjà : $dir\n";
    }
}
