<?php

declare(strict_types=1);

namespace IronFlow\Core\Http;

use IronFlow\Core\View\TwigService;
use IronFlow\Core\Container\Container;

/**
 * Contrôleur de base pour IronFlow
 * 
 * Architecture : Controller -> Service -> Model
 * - Controllers : Point d'entrée HTTP
 * - Services : Logique métier  
 * - Models : Entités + Accès données
 */
abstract class Controller
{
    protected TwigService $twig;
    protected Container $container;
    protected ?Request $request = null;
    
    /**
     * Cache des services injectés pour éviter les résolutions multiples
     */
    private array $serviceCache = [];

    /**
     * Constructeur de base
     */
    public function __construct(TwigService $twig, Container $container, ?Request $request = null)
    {
        $this->twig = $twig;
        $this->container = $container;
        $this->request = $request;
    }

    /**
     * Injection automatique des services via propriété magique
     * 
     * Conventions supportées :
     * - $this->postService => App\Services\PostService
     * - $this->userService => App\Services\UserService  
     * - $this->emailService => App\Services\EmailService
     * - $this->authService => IronFlow\Core\Services\AuthService
     * 
     * @param string $name Nom de la propriété
     * @return mixed Le service résolu
     * @throws \RuntimeException Si le service n'est pas trouvé
     */
    public function __get(string $name)
    {
        // Vérifier le cache d'abord
        if (isset($this->serviceCache[$name])) {
            return $this->serviceCache[$name];
        }

        // Convertir camelCase vers PascalCase
        $serviceClass = $this->convertToPascalCase($name);
        
        // Tentatives de résolution avec différents espaces de noms
        $possibleClasses = $this->getPossibleServiceClasses($serviceClass);
        
        foreach ($possibleClasses as $className) {
            if ($this->container->has($className)) {
                $service = $this->container->make($className);
                // Mettre en cache pour éviter les résolutions futures
                $this->serviceCache[$name] = $service;
                return $service;
            }
        }

        throw new \RuntimeException(
            "Service non trouvé pour la propriété '{$name}'. " .
            "Classes testées : " . implode(', ', $possibleClasses)
        );
    }

    /**
     * Convertit camelCase vers PascalCase
     */
    private function convertToPascalCase(string $name): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $name)));
    }

    /**
     * Génère les classes de services possibles
     * Focus sur les Services principalement
     */
    private function getPossibleServiceClasses(string $serviceClass): array
    {
        $classes = [];
        
        // Priorité aux services dans App\Services
        $classes[] = "App\\Services\\{$serviceClass}";
        
        // Services du core IronFlow
        $classes[] = "IronFlow\\Core\\Services\\{$serviceClass}";
        
        // Services dans d'autres espaces de noms possibles
        $classes[] = "App\\Core\\Services\\{$serviceClass}";
        
        // Nom exact en dernier recours
        $classes[] = $serviceClass;
        
        return array_unique($classes);
    }

    /**
     * Vérifie si un service existe
     */
    public function __isset(string $name): bool
    {
        if (isset($this->serviceCache[$name])) {
            return true;
        }

        $serviceClass = $this->convertToPascalCase($name);
        $possibleClasses = $this->getPossibleServiceClasses($serviceClass);
        
        foreach ($possibleClasses as $className) {
            if ($this->container->has($className)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Rendu d'un template Twig
     */
    protected function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }

    /**
     * Rendu JSON pour les APIs
     */
    protected function json(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /**
     * Redirection HTTP
     */
    protected function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    /**
     * Accès à la requête courante
     */
    public function getRequest(): ?Request
    {
        return $this->request;
    }

    /**
     * Accès direct à un service du container
     */
    public function get(string $service)
    {
        return $this->container->make($service);
    }

    /**
     * Injection manuelle d'un service (utile pour les tests)
     */
    public function setService(string $name, $service): void
    {
        $this->serviceCache[$name] = $service;
    }

    /**
     * Vide le cache des services (utile pour les tests)
     */
    public function clearServiceCache(): void
    {
        $this->serviceCache = [];
    }
}