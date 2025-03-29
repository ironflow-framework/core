<?php

declare(strict_types=1);

namespace IronFlow\Support\Utils;

use Symfony\Component\Translation\Translator as SymfonyTranslator;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Loader\JsonFileLoader;
use IronFlow\Support\Facades\Config;

/**
 * Classe utilitaire pour la gestion des traductions
 */
class Translator
{
   /**
    * Instance du traducteur Symfony
    */
   protected static ?SymfonyTranslator $translator = null;

   /**
    * Langue par défaut
    */
   protected static string $defaultLocale = 'fr';

   /**
    * Langue actuelle
    */
   protected static string $currentLocale = 'fr';

   /**
    * Initialise le traducteur
    */
   public static function initialize(): void
   {
      $locale = Config::get('app.locale', 'fr');
      $fallbackLocale = Config::get('app.fallback_locale', 'en');

      self::$defaultLocale = $locale;
      self::$currentLocale = $locale;

      self::$translator = new SymfonyTranslator($locale);
      self::$translator->setFallbackLocales([$fallbackLocale]);

      // Enregistrement des chargeurs de fichiers de traduction
      self::$translator->addLoader('php', new PhpFileLoader());
      self::$translator->addLoader('yaml', new YamlFileLoader());
      self::$translator->addLoader('yml', new YamlFileLoader());
      self::$translator->addLoader('json', new JsonFileLoader());

      // Chargement des traductions
      self::loadTranslations();
   }

   /**
    * Charge les fichiers de traduction
    */
   protected static function loadTranslations(): void
   {
      $langPath = lang_path();

      if (!is_dir($langPath)) {
         mkdir($langPath, 0755, true);
      }

      // Parcourir tous les dossiers de langues
      $langDirs = glob($langPath . '/*', GLOB_ONLYDIR);

      foreach ($langDirs as $langDir) {
         $locale = basename($langDir);

         // Charger les fichiers PHP
         $phpFiles = glob($langDir . '/*.php');
         foreach ($phpFiles as $file) {
            $domain = basename($file, '.php');
            self::$translator->addResource('php', $file, $locale, $domain);
         }

         // Charger les fichiers YAML/YML
         $yamlFiles = array_merge(
            glob($langDir . '/*.yaml'),
            glob($langDir . '/*.yml')
         );
         foreach ($yamlFiles as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            $domain = basename($file, '.' . $extension);
            self::$translator->addResource($extension, $file, $locale, $domain);
         }

         // Charger les fichiers JSON
         $jsonFiles = glob($langDir . '/*.json');
         foreach ($jsonFiles as $file) {
            $domain = basename($file, '.json');
            self::$translator->addResource('json', $file, $locale, $domain);
         }
      }
   }

   /**
    * Traduit un message
    */
   public static function trans(string $key, array $parameters = [], ?string $domain = null, ?string $locale = null): string
   {
      if (self::$translator === null) {
         self::initialize();
      }

      $domain = $domain ?: 'messages';
      $locale = $locale ?: self::$currentLocale;

      return self::$translator->trans($key, $parameters, $domain, $locale);
   }

   /**
    * Change la locale actuelle
    */
   public static function setLocale(string $locale): void
   {
      self::$currentLocale = $locale;
   }

   /**
    * Récupère la locale actuelle
    */
   public static function getLocale(): string
   {
      return self::$currentLocale;
   }

   /**
    * Récupère la locale par défaut
    */
   public static function getDefaultLocale(): string
   {
      return self::$defaultLocale;
   }

   /**
    * Vérifie si une traduction existe
    */
   public static function has(string $key, ?string $domain = null, ?string $locale = null): bool
   {
      if (self::$translator === null) {
         self::initialize();
      }

      $domain = $domain ?: 'messages';
      $locale = $locale ?: self::$currentLocale;

      // La méthode getCatalogue de Symfony nous permet de vérifier si une clé existe
      $catalogue = self::$translator->getCatalogue($locale);
      return $catalogue->has($key, $domain);
   }
}
