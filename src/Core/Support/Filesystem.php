<?php

declare(strict_types=1);

namespace Ironflow\Core\Support;

class Filesystem
{
    /**
     * Vérifie si un fichier ou un dossier existe.
     *
     * @param string $path
     * @return bool
     */
    public static function exists(string $path): bool
    {
        return file_exists($path);
    }

    public static function mkdir(string $path, int $mode = 0755, bool $recursive = false): bool
    {
        if (self::exists($path)) {
            return true; // Le dossier existe déjà
        }

        return mkdir($path, $mode, $recursive);
    }

    public static function mirror(string $source, string $target, bool $recursive    = false):bool
    {
        return self::copy($source, $target, $recursive);
    }

    /**
     * Écrit du contenu dans un fichier.
     *
     * @param string $path
     * @param string $contents
     * @param int $flags
     * @return bool
     */
    public static function put(string $path, string $contents, int $flags = 0): bool
    {
        return file_put_contents($path, $contents, $flags) !== false;
    }

    /**
     * Copie un fichier.
     *
     * @param string $source
     * @param string $destination
     * @param bool $recursive
     * @return bool
     */
    public static function copy(string $source, string $destination, bool $recursive = false): bool
    {
        if (!self::exists($source)) {
            return false;
        }

        if (self::exists($destination)) {
            self::delete($destination);
        }

        if ($recursive && is_dir($source)) {
            self::mkdir(dirname($destination), 0755, true);
            return copy($source, $destination);
        }

        if (is_dir($source)) {
            return false; // Ne peut pas copier un dossier sans récursivité
        }

        return copy($source, $destination);
    }

    /**
     * Déplace un fichier ou un dossier.
     * Écrase la destination si elle existe.
     *
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function move(string $source, string $destination): bool
    {
        if (!self::exists($source)) {
            return false;
        }

        if (self::exists($destination)) {
            self::delete($destination);
        }

        return rename($source, $destination);
    }

    /**
     * Supprime un fichier ou un dossier récursivement.
     *
     * @param string $path
     * @return bool
     */
    public static function delete(string $path): bool
    {
        if (!self::exists($path)) {
            return false;
        }

        return is_dir($path) ? self::deleteDir($path) : unlink($path);
    }

    /**
     * Supprime un dossier et son contenu récursivement.
     *
     * @param string $path
     * @return bool
     */
    public static function deleteDir(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $items = glob($path . '/*', GLOB_MARK); // GLOB_MARK ajoute un / à la fin des dossiers

        foreach ($items as $item) {
            if (is_dir($item)) {
                self::deleteDir($item);
            } else {
                unlink($item);
            }
        }

        return rmdir($path);
    }
}
