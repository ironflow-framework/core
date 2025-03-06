# IronFlow

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![Build Status](https://img.shields.io/github/workflow/status/ironflow/framework/tests?label=tests)](https://github.com/ironflow/framework/actions)

**IronFlow** est un framework PHP minimaliste, modulaire et puissant, inspiré de Laravel, Django et Ruby on Rails. Il permet de créer rapidement des applications web robustes telles que des sites e-commerce, des plateformes d'e-learning, des blogs et des systèmes de gestion de contenu.

## 📋 Table des matières

- [Présentation](#présentation)
- [Fonctionnalités](#fonctionnalités)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Migrations](#migrations)
- [Développement](#développement)
- [Tests](#tests)
- [Contribuer](#contribuer)
- [License](#license)

## 🚀 Présentation

IronFlow est conçu pour être léger et modulaire. Grâce à son architecture inspirée des meilleurs frameworks modernes, il combine facilité d'utilisation, flexibilité et extensibilité. Le framework fournit tout ce dont un développeur a besoin pour créer une application web performante, avec une interface d'administration intuitive et un ORM puissant.

## ✨ Fonctionnalités

- **Routing puissant** - Un système de routage fluide, inspiré de Laravel
- **Gestion des utilisateurs** - Authentification, gestion des rôles et permissions
- **ORM intégré** - Interagissez facilement avec votre base de données grâce à l'ORM Iron
- **Migrations** - Un système de migration flexible pour gérer vos bases de données
- **Panel d'administration** - Un panel inspiré de Django pour gérer le contenu et les utilisateurs
- **Gestion des fichiers** - Un module pour gérer les fichiers téléchargés et les médias
- **Modulaire** - Intégrez facilement les modules dont vous avez besoin (CMS, e-commerce, etc.)

## 📋 Prérequis

- PHP 7.4 ou supérieur
- Composer
- Une base de données MySQL, PostgreSQL ou SQLite
- Un serveur web (Apache, Nginx, etc.)

## 💿 Installation

### Via Composer

```bash
composer create-project ironflow/ironflow mon-projet
cd mon-projet
```

### Manuellement

1. **Cloner le repository**:

   ```bash
   git clone https://github.com/ironflow/framework.git
   cd framework
   ```

2. **Installer les dépendances**:

   ```bash
   composer install
   ```

3. **Configurer l'environnement**:

   ```bash
   cp .env.example .env
   php forge key:generate
   ```

4. **Lancer le serveur**:

   ```bash
   php forge serve
   ```

   Votre application est maintenant accessible à l'adresse [http://localhost:8000](http://localhost:8000)

## ⚙️ Configuration

### Base de données

Configurez votre connexion à la base de données dans le fichier `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ironflow
DB_USERNAME=root
DB_PASSWORD=password
```

### Email

```ini
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=username
MAIL_PASSWORD=password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@example.com
```

## 🛠️ Utilisation

### Créer une route

```php
// routes/web.php
Route::get('/accueil', [HomeController::class, 'index']);
Route::post('/articles', [ArticleController::class, 'store']);
Route::resource('users', 'UserController');
```

### Créer un contrôleur

```bash
php forge generate:controller HomeController
```

```php
// app/Controllers/HomeController.php
namespace App\Controllers;

use Forge\Http\Controllers\BaseController;

class HomeController extends BaseController
{
    public function index(Request $request)
    {
        return $this->view($request, 'home.index', [
            'title' => 'Accueil'
        ]);
    }
}
```

### Créer un modèle

```bash
php artisan generate:model Article
```

```php
// app/Models/Article.php
namespace App\Models;

class Article extends Model
{
    protected $fillable = ['title', 'content', 'author_id'];
    
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

## 🗃️ Migrations

### Créer une migration

```bash
php artisan make:migration create_articles_table
```

```php
// database/migrations/2023_01_15_create_articles_table.php
public function up()
{
    Schema::create('articles', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('content');
        $table->foreignId('author_id')->constrained('users');
        $table->timestamps();
    });
}
```

### Exécuter les migrations

```bash
php artisan migrate
```

## 🧩 Commandes Artisan

IronFlow fournit plusieurs commandes artisan pour développer efficacement:

```bash
php artisan list                 # Liste toutes les commandes disponibles
php artisan make:controller      # Crée un nouveau contrôleur
php artisan make:model           # Crée un nouveau modèle
php artisan make:migration       # Crée une nouvelle migration
php artisan migrate              # Exécute les migrations en attente
php artisan db:seed              # Alimente la base de données avec des données de test
php artisan cache:clear          # Vide le cache de l'application
php artisan key:generate         # Génère une clé d'application
```

## 🧪 Tests

Les tests sont exécutés via PHPUnit:

```bash
php artisan test
```

Vous pouvez aussi exécuter des tests spécifiques:

```bash
php artisan test --filter=UserTest
```

## 🤝 Contribuer

Nous accueillons les contributions avec plaisir! Voici comment participer:

1. Fork du dépôt
2. Créez votre branche (`git checkout -b feature/amazing-feature`)
3. Committez vos changements (`git commit -m 'Add some amazing feature'`)
4. Poussez vers la branche (`git push origin feature/amazing-feature`)
5. Ouvrez une Pull Request

Pour plus de détails, consultez notre [guide de contribution](CONTRIBUTING.md).

## 📄 License

IronFlow est sous licence [MIT](LICENSE).

---

<p align="center">Développé avec ❤️ par l'équipe IronFlow</p>
