<?php

namespace IronFlow\Core\View;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\ExtensionInterface;
use Twig\TwigFunction;
use Twig\TwigFilter;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use InvalidArgumentException;

class TwigService
{
    protected Environment $twig;
    protected FilesystemLoader $loader;
    protected array $globalVariables = [];
    protected bool $debug = false;

    public function __construct(
        string|array $viewsPath,
        array $options = [],
        bool $debug = false
    ) {
        $this->debug = $debug;
        
        // Configuration du loader avec support de multiples chemins
        $paths = is_array($viewsPath) ? $viewsPath : [$viewsPath];
        $this->loader = new FilesystemLoader($paths);
        
        // Configuration par défaut de l'environnement Twig
        $defaultOptions = [
            'cache' => !$debug ? sys_get_temp_dir() . '/twig_cache' : false,
            'debug' => $debug,
            'auto_reload' => $debug,
            'strict_variables' => $debug,
        ];
        
        $twigOptions = array_merge($defaultOptions, $options);
        $this->twig = new Environment($this->loader, $twigOptions);
        
        // Ajout de l'extension debug si nécessaire
        if ($debug) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
        
        $this->registerDefaultFunctions();
        $this->registerDefaultFilters();
    }

    /**
     * Rend un template avec les données fournies
     */
    public function render(string $template, array $data = []): string
    {
        try {
            // Fusion avec les variables globales
            $data = array_merge($this->globalVariables, $data);
            return $this->twig->render($template, $data);
        } catch (LoaderError $e) {
            throw new InvalidArgumentException("Template '$template' introuvable: " . $e->getMessage(), 0, $e);
        } catch (RuntimeError | SyntaxError $e) {
            throw new InvalidArgumentException("Erreur lors du rendu du template '$template': " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Vérifie si un template existe
     */
    public function exists(string $template): bool
    {
        return $this->loader->exists($template);
    }

    /**
     * Ajoute un chemin de vues
     */
    public function addPath(string $path, string $namespace = FilesystemLoader::MAIN_NAMESPACE): self
    {
        $this->loader->addPath($path, $namespace);
        return $this;
    }

    /**
     * Définit une variable globale disponible dans tous les templates
     */
    public function addGlobal(string $name, mixed $value): self
    {
        $this->globalVariables[$name] = $value;
        $this->twig->addGlobal($name, $value);
        return $this;
    }

    /**
     * Définit plusieurs variables globales
     */
    public function addGlobals(array $globals): self
    {
        foreach ($globals as $name => $value) {
            $this->addGlobal($name, $value);
        }
        return $this;
    }

    /**
     * Ajoute une extension Twig
     */
    public function addExtension(ExtensionInterface $extension): self
    {
        $this->twig->addExtension($extension);
        return $this;
    }

    /**
     * Ajoute une fonction Twig personnalisée
     */
    public function addFunction(string $name, callable $callback, array $options = []): self
    {
        $function = new TwigFunction($name, $callback, $options);
        $this->twig->addFunction($function);
        return $this;
    }

    /**
     * Ajoute un filtre Twig personnalisé
     */
    public function addFilter(string $name, callable $callback, array $options = []): self
    {
        $filter = new TwigFilter($name, $callback, $options);
        $this->twig->addFilter($filter);
        return $this;
    }

    /**
     * Configure le cache
     */
    public function setCache(string|false $cache): self
    {
        $this->twig->setCache($cache);
        return $this;
    }

    /**
     * Vide le cache
     */
    public function clearCache(): self
    {
        if ($cache = $this->twig->getCache()) {
            $this->twig->removeCache($cache);
        }
        return $this;
    }

    /**
     * Active ou désactive le mode debug
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        
        if ($debug && !$this->twig->hasExtension(\Twig\Extension\DebugExtension::class)) {
            $this->twig->addExtension(new \Twig\Extension\DebugExtension());
        }
        
        return $this;
    }

    /**
     * Retourne l'environnement Twig
     */
    public function getTwig(): Environment
    {
        return $this->twig;
    }

    /**
     * Retourne le loader
     */
    public function getLoader(): FilesystemLoader
    {
        return $this->loader;
    }

    /**
     * Retourne les variables globales
     */
    public function getGlobals(): array
    {
        return $this->globalVariables;
    }

    /**
     * Vérifie si le mode debug est activé
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * Charge et rend un template de manière statique
     */
    public function renderString(string $template, array $data = []): string
    {
        try {
            $data = array_merge($this->globalVariables, $data);
            return $this->twig->createTemplate($template)->render($data);
        } catch (SyntaxError $e) {
            throw new InvalidArgumentException("Erreur de syntaxe dans le template: " . $e->getMessage(), 0, $e);
        } catch (RuntimeError $e) {
            throw new InvalidArgumentException("Erreur d'exécution du template: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Enregistre les fonctions par défaut
     */
    protected function registerDefaultFunctions(): void
    {
        // Fonction pour générer des URLs (à adapter selon votre routeur)
        $this->addFunction('url', function (string $route, array $params = []) {
            // Implémentation basique - à adapter selon votre système de routage
            $query = !empty($params) ? '?' . http_build_query($params) : '';
            return $route . $query;
        });

        // Fonction pour échapper les données JSON
        $this->addFunction('json_encode', function ($data) {
            return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        });

        // Fonction pour formater les dates
        $this->addFunction('date_format', function ($date, string $format = 'Y-m-d H:i:s') {
            if (is_string($date)) {
                $date = new \DateTime($date);
            }
            return $date instanceof \DateTime ? $date->format($format) : '';
        });
    }

    /**
     * Enregistre les filtres par défaut
     */
    protected function registerDefaultFilters(): void
    {
        // Filtre pour tronquer le texte
        $this->addFilter('truncate', function (string $text, int $length = 100, string $suffix = '...') {
            return strlen($text) > $length ? substr($text, 0, $length) . $suffix : $text;
        });

        // Filtre pour slug
        $this->addFilter('slug', function (string $text) {
            $text = strtolower($text);
            $text = preg_replace('/[^a-z0-9\-]/', '-', $text);
            return trim(preg_replace('/-+/', '-', $text), '-');
        });

        // Filtre pour formater les tailles de fichier
        $this->addFilter('filesize', function (int $bytes, int $precision = 2) {
            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $bytes = max($bytes, 0);
            $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
            $pow = min($pow, count($units) - 1);
            $bytes /= pow(1024, $pow);
            return round($bytes, $precision) . ' ' . $units[$pow];
        });
    }
}