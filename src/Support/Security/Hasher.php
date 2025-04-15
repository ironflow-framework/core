<?php

namespace IronFlow\Support\Security;

/**
 * Classe utilitaire pour le hachage de données
 */
class Hasher
{
    /**
     * Hache une valeur avec Bcrypt
     * 
     * @param string $value
     * @return string
     */
    public static function hash(string $value): string
    {
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Vérifie qu'une valeur correspond à un hash
     * 
     * @param string $value
     * @param string $hash
     * @return bool
     */
    public static function verify(string $value, string $hash): bool
    {
        return password_verify($value, $hash);
    }

    /**
     * Alias pour hash()
     */
    public static function make(string $value): string
    {
        return self::hash($value);
    }
}
