<?php

declare(strict_types=1);

namespace IronFlow\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Classe de base pour les commandes console d'IronFlow
 */
abstract class Command extends SymfonyCommand
{
    /**
     * Code de succès
     */
    public const SUCCESS = 0;

    /**
     * Code d'échec
     */
    public const FAILURE = 1;

    /**
     * Code d'erreur invalide
     */
    public const INVALID = 2;
}
