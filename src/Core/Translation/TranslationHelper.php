<?php

declare(strict_types=1);

namespace IronFlow\Core\Translation;


/**
 * Helper class pour les fonctions globales
 */
class TranslationHelper
{
    protected static ?Translator $instance = null;

    public static function setInstance(Translator $translator): void
    {
        self::$instance = $translator;
    }

    public static function getInstance(): ?Translator
    {
        return self::$instance;
    }
}
