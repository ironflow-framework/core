# IronFlow Framework Core

IronFlow est un framework PHP moderne, modulaire et API-first, conçu pour la création rapide d'applications web et microservices robustes.

## Sommaire

* [Installation](#installation)
* [Architecture](#architecture)
* [Démarrage rapide](#démarrage-rapide)
* [Exemples d'utilisation](#exemples-dutilisation)
* [ORM & Migrations](#orm--migrations)
* [Tests](#tests)
* [Extensibilité](#extensibilité)
* [Sécurité](#sécurité)
* [Contribuer](#contribuer)

## Installation

```bash
composer require ironflow/core
```

## Architecture

```bash
src/
  Core/
    Kernel.php           # Cœur du framework
    Console/             # Console et commandes personnalisées
    Container/           # Container d'injection de dépendances
    Database/            # ORM, QueryBuilder, modèles
    Exception/           # Gestion des exceptions et erreurs
    Http/                # Requêtes, réponses, routeur, middlewares
    Module/              # Système de modules/plugins
```

## Démarrage rapide

```php
use IronFlow\Core\Application;

$app = new Application();
$response = $app->handleRequest();
$response->send();
```

## Exemples d'utilisation

### Définir une route

```php
$router->get('/hello', function() {
    return 'Hello World!';
});
```

### Utiliser un middleware

```php
$kernel->addMiddleware(new \IronFlow\Core\Http\Middleware\CorsMiddleware());
```

### Créer un module

```php
class BlogModule extends ModuleProvider {
    public function register() {
        // ...
    }
}
$moduleManager->register(new BlogModule());
```

### Rendu Twig (template)

```php
use IronFlow\Core\View\TwigService;
$twig = new TwigService(__DIR__ . '/views');
echo $twig->render('home.html.twig', ['user' => 'Kinga']);
```

### Validation métier avec Respect\Validation

```php
use IronFlow\Core\Validation\Validator;
use Respect\Validation\Validator as v;

$validator = new Validator();
$validator->setRules([
    'email' => v::email()->notEmpty(),
    'age'   => v::intVal()->min(18)
]);
$data = ['email' => 'test@example.com', 'age' => 20];
if (!$validator->validate($data)) {
    print_r($validator->errors());
}
```

### Logging avec Monolog

```php
use IronFlow\Core\Logger\LoggerService;
$logger = new LoggerService();
$logger->info('Nouvelle connexion utilisateur', ['user' => 'Kinga']);
$logger->error('Erreur critique', ['exception' => $e]);
```

### Injection automatique via le container

```php
$container->singleton(TwigService::class, fn() => new TwigService(__DIR__.'/../views'));
$container->singleton(Validator::class, fn() => new Validator());
$container->singleton(LoggerService::class, fn() => new LoggerService());

class UserController {
    public function __construct(
        TwigService $twig,
        Validator $validator,
        LoggerService $logger
    ) { /* ... */ }
}
```

## ORM & Migrations

IronFlow inclut un ORM léger et fluide, similaire à Laravel Blueprint, nommé `Anvil`, et un système de migration inspiré d'Eloquent.

### Exemple de migration

```php
use IronFlow\Core\Database\Anvil;
use IronFlow\Core\Database\Migration;
use IronFlow\Core\Database\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Anvil $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::drop('users');
    }
};
```

### Méthodes disponibles avec Anvil

* `id()`
* `string($name, $length = 255)`
* `integer($name)`
* `boolean($name)`
* `timestamp($name)`
* `timestamps()`
* `text($name)`
* `enum($name, array $values)`
* `foreign($column)->references($target)->on($table)`
* `nullable()`
* `default($value)`
* `unique()`
* `index()`

## Tests

Lancez les tests unitaires :

```bash
vendor\bin\phpunit
```

## Extensibilité

### Modules/Plugins

* Ajoutez vos modules dans `src/Core/Module/` en créant une classe qui étend `ModuleProvider`.
* Exemple d'ajout d'un module externe :

```php
use IronFlow\Core\Module\ModuleManager;
use App\Modules\Blog\BlogModule;

$moduleManager = new ModuleManager($container);
$moduleManager->register(new BlogModule());
$moduleManager->bootModules();
```

* Un module doit implémenter la méthode `register(Container $container)` et peut surcharger `boot()` pour l'initialisation.

### Hooks & Events

* Le cœur fournit un dispatcher d'événements (`EventDispatcher`).
* Exemple d'écoute et de déclenchement d'un hook :

```php
use IronFlow\Core\Events\EventDispatcher;

$events = new EventDispatcher();
$events->listen('user.registered', function($user) {
    // Action personnalisée
});

// Plus tard dans le code :
$events->dispatch('user.registered', $user);
```

* Les modules peuvent s'abonner à des événements du cœur ou en émettre pour permettre l'extension sans modifier le code principal.

## Sécurité

* Middlewares pour CORS, CSRF, XSS, validation d'entrée
* Gestion centralisée des erreurs et logs

## Documentation API

* Les classes et méthodes publiques sont documentées avec des DocBlocks PHP
* Générez la documentation avec [phpDocumentor](https://www.phpdoc.org/)

## Contribuer

Les contributions sont les bienvenues ! Merci de lire le guide de contribution dans `CONTRIBUTING.md`.
