<?php

namespace IronFlow\Core\Translation;


use IronFlow\Core\Translation\TranslationAdapter;


class Translator
{
    protected ?TranslationAdapter $adapter = null;
    protected string $currentLocale = 'fr';
    protected string $fallbackLocale = 'en';
    protected array $translations = [];
    protected array $loadedFiles = [];
    protected string $translationsPath;
    protected array $pluralRules = [];

    public function __construct(string $translationsPath = 'translations', string $locale = 'fr', ?TranslationAdapter $adapter = null)
    {
        $this->translationsPath = rtrim($translationsPath, '/');
        $this->currentLocale = $locale;
        $this->adapter = $adapter;
        $this->initializePluralRules();
    }

    /**
     * Définit la locale courante
     */

    public function setLocale(string $locale): self
    {
        $this->currentLocale = $locale;
        if ($this->adapter) {
            $this->adapter->setLocale($locale);
        }
        return $this;
    }

    /**
     * Retourne la locale courante
     */

    public function getLocale(): string
    {
        return $this->adapter ? $this->adapter->getLocale() : $this->currentLocale;
    }

    /**
     * Définit la locale de fallback
     */

    public function setFallbackLocale(string $locale): self
    {
        $this->fallbackLocale = $locale;
        // Optionnel : si adapter supporte fallback, l'ajouter ici
        return $this;
    }

    /**
     * Charge un fichier de traduction
     */
    public function loadTranslations(string $domain = 'messages', string $locale = null): self
    {
        $locale = $locale ?? $this->currentLocale;
        $cacheKey = "{$locale}.{$domain}";

        if (isset($this->loadedFiles[$cacheKey])) {
            return $this;
        }

        $filePath = $this->getTranslationFilePath($domain, $locale);

        if (file_exists($filePath)) {
            $translations = $this->loadTranslationFile($filePath);
            $this->translations[$locale][$domain] = array_merge(
                $this->translations[$locale][$domain] ?? [],
                $translations
            );
            $this->loadedFiles[$cacheKey] = true;
        }

        return $this;
    }

    /**
     * Traduit une clé
     */

    public function trans(string $key, array $parameters = [], string $domain = 'messages', string $locale = null): string
    {
        if ($this->adapter) {
            return $this->adapter->trans($key, $parameters, $domain, $locale);
        }
        // fallback maison
        $locale = $locale ?? $this->currentLocale;
        $this->loadTranslations($domain, $locale);
        $translation = $this->findTranslation($key, $domain, $locale);
        if ($translation === null) {
            if ($locale !== $this->fallbackLocale) {
                $this->loadTranslations($domain, $this->fallbackLocale);
                $translation = $this->findTranslation($key, $domain, $this->fallbackLocale);
            }
            if ($translation === null) {
                return $key;
            }
        }
        return $this->interpolate($translation, $parameters);
    }

    /**
     * Traduit avec gestion des pluriels
     */

    public function transChoice(string $key, int $count, array $parameters = [], string $domain = 'messages', string $locale = null): string
    {
        if ($this->adapter) {
            return $this->adapter->transChoice($key, $count, $parameters, $domain, $locale);
        }
        // fallback maison
        $locale = $locale ?? $this->currentLocale;
        $this->loadTranslations($domain, $locale);
        $translation = $this->findTranslation($key, $domain, $locale);
        if ($translation === null) {
            if ($locale !== $this->fallbackLocale) {
                $this->loadTranslations($domain, $this->fallbackLocale);
                $translation = $this->findTranslation($key, $domain, $this->fallbackLocale);
            }
            if ($translation === null) {
                return $key;
            }
        }
        $parameters['count'] = $count;
        $pluralForm = $this->selectPluralForm($translation, $count, $locale);
        return $this->interpolate($pluralForm, $parameters);
    }

    /**
     * Ajoute des traductions en mémoire
     */
    public function addTranslations(array $translations, string $domain = 'messages', string $locale = null): self
    {
        $locale = $locale ?? $this->currentLocale;

        if (!isset($this->translations[$locale][$domain])) {
            $this->translations[$locale][$domain] = [];
        }

        $this->translations[$locale][$domain] = array_merge(
            $this->translations[$locale][$domain],
            $translations
        );

        return $this;
    }

    /**
     * Vérifie si une traduction existe
     */
    public function has(string $key, string $domain = 'messages', string $locale = null): bool
    {
        $locale = $locale ?? $this->currentLocale;
        $this->loadTranslations($domain, $locale);

        return $this->findTranslation($key, $domain, $locale) !== null;
    }

    /**
     * Retourne toutes les traductions pour un domaine
     */
    public function all(string $domain = 'messages', string $locale = null): array
    {
        $locale = $locale ?? $this->currentLocale;
        $this->loadTranslations($domain, $locale);

        return $this->translations[$locale][$domain] ?? [];
    }

    /**
     * Trouve une traduction par clé
     */
    protected function findTranslation(string $key, string $domain, string $locale): ?string
    {
        if (!isset($this->translations[$locale][$domain])) {
            return null;
        }

        $translations = $this->translations[$locale][$domain];

        // Support des clés avec notation pointée (ex: user.name)
        if (strpos($key, '.') !== false) {
            $keys = explode('.', $key);
            $value = $translations;

            foreach ($keys as $k) {
                if (!is_array($value) || !isset($value[$k])) {
                    return null;
                }
                $value = $value[$k];
            }

            return is_string($value) ? $value : null;
        }

        return isset($translations[$key]) && is_string($translations[$key])
            ? $translations[$key]
            : null;
    }

    /**
     * Interpole les paramètres dans la traduction
     */
    protected function interpolate(string $translation, array $parameters): string
    {
        if (empty($parameters)) {
            return $translation;
        }

        $replacements = [];
        foreach ($parameters as $key => $value) {
            $replacements['{' . $key . '}'] = (string) $value;
            $replacements['{{' . $key . '}}'] = (string) $value;
            $replacements[':' . $key] = (string) $value;
        }

        return strtr($translation, $replacements);
    }

    /**
     * Sélectionne la forme plurielle appropriée
     */
    protected function selectPluralForm(string $translation, int $count, string $locale): string
    {
        // Si pas de forme plurielle, retourner tel quel
        if (strpos($translation, '|') === false) {
            return $translation;
        }

        $forms = explode('|', $translation);
        $rule = $this->pluralRules[$locale] ?? $this->pluralRules['en'];

        $index = $rule($count);

        return isset($forms[$index]) ? trim($forms[$index]) : trim($forms[0]);
    }

    /**
     * Initialise les règles de pluriel pour différentes langues
     */
    protected function initializePluralRules(): void
    {
        $this->pluralRules = [
            // Français : 0-1 = singulier, >1 = pluriel
            'fr' => function (int $count): int {
                return ($count <= 1) ? 0 : 1;
            },

            // Anglais : 1 = singulier, autres = pluriel
            'en' => function (int $count): int {
                return ($count === 1) ? 0 : 1;
            },

            // Espagnol : même règle que l'anglais
            'es' => function (int $count): int {
                return ($count === 1) ? 0 : 1;
            },

            // Allemand : même règle que l'anglais
            'de' => function (int $count): int {
                return ($count === 1) ? 0 : 1;
            },

            // Italien : même règle que l'anglais
            'it' => function (int $count): int {
                return ($count === 1) ? 0 : 1;
            },

            // Russe : règles complexes
            'ru' => function (int $count): int {
                if ($count % 10 === 1 && $count % 100 !== 11) {
                    return 0; // 1, 21, 31, ... (mais pas 11)
                }
                if (in_array($count % 10, [2, 3, 4]) && !in_array($count % 100, [12, 13, 14])) {
                    return 1; // 2-4, 22-24, 32-34, ... (mais pas 12-14)
                }
                return 2; // 0, 5-20, 25-30, ...
            },

            // Polonais : règles complexes similaires au russe
            'pl' => function (int $count): int {
                if ($count === 1) {
                    return 0;
                }
                if (in_array($count % 10, [2, 3, 4]) && !in_array($count % 100, [12, 13, 14])) {
                    return 1;
                }
                return 2;
            },
        ];
    }

    /**
     * Ajoute une règle de pluriel personnalisée
     */
    public function addPluralRule(string $locale, callable $rule): self
    {
        $this->pluralRules[$locale] = $rule;
        return $this;
    }

    /**
     * Charge un fichier de traduction
     */
    protected function loadTranslationFile(string $filePath): array
    {
        return require $filePath;
    }

    /**
     * Génère le chemin d'un fichier de traduction
     */
    protected function getTranslationFilePath(string $domain, string $locale): string
    {
        // Essaie différents formats de fichier
        $extensions = ['php', 'json', 'yaml', 'yml'];

        foreach ($extensions as $ext) {
            $filePath = "{$this->translationsPath}/{$locale}/{$domain}.{$ext}";
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        // Par défaut, retourne le chemin PHP
        return "{$this->translationsPath}/{$locale}/{$domain}.php";
    }

    /**
     * Méthodes magiques pour une utilisation plus simple
     */
    public function __call(string $method, array $args): string
    {
        // Permet d'appeler $translator->messages('key') au lieu de $translator->trans('key', [], 'messages')
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $args);
        }

        // Traite le nom de méthode comme un domaine
        $key = $args[0] ?? '';
        $parameters = $args[1] ?? [];
        $locale = $args[2] ?? null;

        return $this->trans($key, $parameters, $method, $locale);
    }
}
