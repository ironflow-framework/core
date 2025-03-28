# Standards d'Espace de Noms IronFlow

Ce document sert de guide pour standardiser les espaces de noms du framework IronFlow.

## Standards d'Espace de Noms

### 1. Base de données et ORM

- `\IronFlow\Iron` - Espace de noms principal pour les fonctionnalités de base de données et ORM
- `\IronFlow\Iron\Model` - Pour les modèles de base
- `\IronFlow\Iron\Relations` - Pour les classes de relations
- `\IronFlow\Iron\Query` - Pour les classes liées aux requêtes
- `\IronFlow\Iron\Migrations` - Pour les migrations
- `\IronFlow\Iron\Schema` - Pour les classes liées au schéma de base de données
- `\IronFlow\Iron\Factories` - Pour les factories de test

### 2. Validations et formulaires

- `\IronFlow\Validation` - Pour les validateurs
- `\IronFlow\Furnace` - Pour les formulaires et composants d'interface
- `\IronFlow\Furnace\Traits` - Pour les traits liés aux formulaires

### 3. HTTP et routage

- `\IronFlow\Http` - Pour les classes HTTP
- `\IronFlow\Routing` - Pour le routage

### 4. CraftPanel (admin)

- `\IronFlow\CraftPanel` - Pour les fonctionnalités d'administration

### 5. Core et Support

- `\IronFlow\Core` - Pour les classes core
- `\IronFlow\Application` - Pour les classes liées à l'application
- `\IronFlow\Support` - Pour les classes utilitaires

## Règles de Migration

Pour assurer la cohérence, tous les nouveaux développements doivent suivre cette structure.
Les classes existantes sont en cours de migration de `\IronFlow\Database` vers `\IronFlow\Iron`.

### Comment utiliser les classes pendant la migration

Les développeurs doivent utiliser les nouvelles classes sous l'espace de noms `\IronFlow\Iron`:

```php
// Ancienne façon (déconseillée)
use IronFlow\Database\Model;

// Nouvelle façon (recommandée)
use IronFlow\Iron\Model;
```

## Classes d'alias pour la compatibilité

Des classes d'alias sont disponibles pour assurer la compatibilité avec le code existant:

```php
namespace IronFlow\Database;

/**
 * @deprecated Utiliser IronFlow\Iron\Model à la place
 */
class Model extends \IronFlow\Iron\Model
{
    // Classe d'alias pour la compatibilité
}
```
