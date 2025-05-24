<?php

// Détection de l'OS
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';

if ($isWindows) {
    echo "🔍 OS détecté : Windows. Pas de chmod nécessaire.\n";
} else {
    echo "🔍 OS détecté : Unix/Linux/Mac. Attribution des droits d'exécution.\n";
    $scriptPath = __DIR__ . '/../vendor/ironflow/framework/scripts/run_npm_install.sh';

    if (file_exists($scriptPath)) {
        chmod($scriptPath, 0755);
        echo "✅ Script rendu exécutable : $scriptPath\n";
    } else {
        echo "⚠️ Script non trouvé : $scriptPath\n";
    }
}

