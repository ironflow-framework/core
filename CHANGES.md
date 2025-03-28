# Historique des Changements

## [28/03/25]

### CraftPanel
- Création des composants de base (navbar, sidebar) avec un design moderne et support des thèmes clair/sombre
- Mise en place de la structure de base des vues Twig
- Intégration des composants existants (Furnace, Crucible) pour les formulaires et validations
- Utilisation du système d'authentification existant
- Création de la commande d'installation (craftpanel:install)
- Création de la commande de création d'administrateur (craftpanel:make-admin)
- Création de la commande d'enregistrement de modèle (craftpanel:register)
- Implémentation du middleware d'authentification
- Création du contrôleur CraftPanel avec toutes les fonctionnalités CRUD
- Génération automatique des vues pour chaque modèle
- Support des permissions granulaires par modèle
- Intégration complète avec le système de cache (Hammer)
- Mise à jour du fichier CHANGES.md pour documenter les modifications apportées au CraftPanel

### Cache (Hammer)
- Refactoring complet du système de cache pour respecter la nomenclature "Forge"
- Renommage du namespace de `Cache` à `Hammer`
- Implémentation complète de la classe `Hammer` avec pattern Singleton
- Amélioration du `HammerManager` pour gérer différents drivers
- Implémentation complète du `FileDriver` avec gestion avancée des expirations
- Implémentation du `RedisDriver` pour le stockage Redis
- Implémentation du `MemcachedDriver` pour le stockage Memcached
- Création de tests unitaires pour valider le fonctionnement du système de cache
- Documentation complète du code avec PHPDoc
- Correction des méthodes manquantes dans les interfaces et les implémentations

### Système de formulaire (Furnace)  
- Mise à jour du système de formulaire  
- Ajoute d'une classe pour gérer le lien entre le formulaire et le model  

## [27/03/25]

### Application
- Création de la classe Container

### Database et Iron ORM
- Amelioration de l'ORM

### Craft Panel (A revoir car supprimer)
- Création des commandes liées au craftpanel
  - Installation et configuration    
- Création du controller

### Harmonisation du Framework
- Mise en place de la nomenclature "Forge" :
  - Iron (ORM) pour la gestion de la base de données
  - Anvil (Schema) pour les migrations
  - Crucible (Validation) pour la validation des données
  - Hammer (Cache) pour le système de cache
  - Furnace (Forms) pour la gestion des formulaires
  - CraftPanel pour l'administration

### Cache (Hammer)
- Implémentation du système de cache avec pattern Singleton
- Création de l'interface CacheDriverInterface
- Implémentation des drivers :
  - FileDriver pour le stockage sur fichier
  - RedisDriver pour le stockage Redis
- Support de l'expiration des données en cache
- Gestion des erreurs et exceptions

### Formulaires (Furnace)
- Renommage du système de formulaires en "Furnace"
- Implémentation du trait HasForm pour lier les formulaires aux modèles
- Création de la classe de base FormComponent
- Amélioration des composants existants :
  - Input (text, email, password, etc.)
  - Select
  - Textarea
  - Checkbox
  - Radio
- Support des validations via Crucible
- Intégration avec Tailwind CSS pour le style par défaut
- Suppression du dossier Form situé dans le dossier View
- Ajout des composants de formulaire prédéfinis :
  - LoginForm pour l'authentification
  - ContactForm pour les formulaires de contact
  - CheckoutForm pour les processus de paiement
- Amélioration du trait HasForm :
  - Support des règles de validation
  - Messages d'erreur personnalisés
  - Gestion des erreurs de validation
- Structuration complète des fichiers :
  - /src/Furnace/Forms/Auth pour les formulaires d'authentification
  - /src/Furnace/Forms/Checkout pour les formulaires de paiement
  - /src/Furnace/Forms pour les formulaires génériques

### Système de Component
- Création d'une nouvelle structure pour les composants :
  - `src/View/Components/` : Composants de base
  - `src/View/Components/Forms/` : Composants de formulaire
  - `src/View/Components/Layout/` : Composants de mise en page
  - `src/View/Components/UI/` : Composants d'interface utilisateur

### Système de Validation (Crucible)
- [x] Création de la classe `Validator` pour la validation des données
- [x] Implémentation des règles de validation de base :
  - required
  - email
  - min
  - max
  - numeric
  - alpha
  - alphanumeric
  - file (type, size, mime)
  - date (format, min, max)
  - color (format)
- [x] Support des messages d'erreur personnalisés
- [x] Affichage des erreurs dans les composants de formulaire

- [x] Créer les composants de formulaire suivants :
  - [x] Input
  - [x] Textarea
  - [x] Select
  - [x] Checkbox
  - [x] Radio
  - [x] File upload
  - [x] Date picker
  - [x] Color picker
- [x] Ajouter plus de règles de validation
- [x] Créer des composants de mise en page
- [x] Développer des composants UI réutilisables
- [x] Ajouter des tests unitaires pour les composants
- [x] Créer une documentation détaillée des composants
- [x] Implémenter des exemples d'utilisation

### Système d'authentification
- Implémentation du système d'authentification moderne et sécurisé :
  - Pattern Singleton pour AuthManager
  - Support de plusieurs guards (session, token, oauth)
  - Intégration avec le système de formulaires (Furnace)
- Création des guards :
  - SessionGuard pour l'authentification web
  - TokenGuard pour l'authentification API
  - OAuthGuard pour l'authentification sociale
- Support OAuth :
  - Intégration de League OAuth2 Client
  - Support de Google, GitHub et Facebook
  - Configuration flexible des providers
  - Gestion sécurisée des états
- Middleware d'authentification
- Commande d'installation auth:install :
  - Création des migrations (users, password_resets)
  - Installation des vues d'authentification
  - Configuration des routes
  - Génération des contrôleurs
