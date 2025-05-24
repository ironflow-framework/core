#!/bin/bash

echo "🔍 Vérification de la présence de npm..."

if command -v npm &> /dev/null
then
    echo "✅ npm détecté. Installation des packages..."
    npm install
else
    echo "⚠️ npm n'est pas installé sur cette machine. Installation des packages JS ignorée."
fi
