# Historique des Changements

## [29/03/25]

### Améliorations du système de composants

- Mise à jour complète des composants existants avec un style cohérent
  - Harmonisation des styles avec l'identité visuelle de la forge
  - Ajout de variantes pour tous les composants UI (taille, couleur, état)
  - Support complet des thèmes clair/sombre
- Optimisation des performances des composants
  - Réduction du temps de rendu des composants complexes
  - Optimisation du chargement des ressources CSS/JS
  - Mise en cache intelligente des composants fréquemment utilisés
- Documentation complète des composants
  - Génération d'une documentation automatique à partir des PHPDoc
  - Exemples d'utilisation pour chaque composant
  - Guide des meilleures pratiques

### Sécurité globale du framework

- Audit complet de la sécurité du framework
  - Analyse des vulnérabilités potentielles
  - Correction des failles de sécurité identifiées
  - Renforcement de la protection contre les attaques XSS et CSRF
- Implémentation de mécanismes de protection avancés
  - Protection contre les attaques par force brute
  - Limitation du taux de requêtes
  - Détection des comportements suspects
- Mise à jour des dépendances sensibles
  - Mise à jour vers les dernières versions sécurisées des packages
  - Réduction des dépendances non essentielles

### Refactoring de la classe Controller

- Correction des bugs identifiés dans la classe Controller
  - Résolution des problèmes de gestion des erreurs
  - Correction des fuites de mémoire potentielles
  - Amélioration de la gestion des réponses HTTP
- Ajout de nouvelles fonctionnalités
  - Support amélioré pour les réponses JSON
  - Méthodes utilitaires pour la gestion des redirections
  - Support des validations intégrées
- Optimisation des performances
  - Réduction de l'empreinte mémoire
  - Amélioration du temps de traitement des requêtes

### Commandes CLI et Console

- Révision complète de la commande MakeModel et MakeForm
  - Support amélioré pour la génération de modèles avec relations
  - Adaptation de la commande MakeForm pour différents cas d'utilisation
  - Options pour personnaliser les champs générés
- Amélioration du système de commandes
  - Structure plus cohérente pour toutes les commandes
  - Meilleure gestion des options et arguments
  - Documentation améliorée des commandes
- Adaptation des commandes d'authentification
  - Harmonisation avec le style des autres commandes CLI
  - Options avancées pour la configuration de l'authentification
  - Support des différents types de guards


### Corrections du système Vibe et améliorations

- Implémentation complète de la classe `MediaManager` (auparavant vide)
  - Ajout du modèle Singleton pour assurer une instance unique
  - Méthodes robustes pour la validation, le téléversement et la suppression des fichiers
  - Extraction avancée des métadonnées pour différents types de médias (images, vidéos, audio)
  - Gestion automatique des miniatures avec différentes tailles
  - Détection intelligente des types de médias basée sur l'extension et le MIME type
- Création d'un contrôleur `MediaController` complet
  - Endpoints RESTful pour la gestion des médias (liste, affichage, téléversement, téléchargement, suppression)
  - Gestion appropriée des erreurs avec des messages explicites
  - Support des requêtes AJAX pour une expérience utilisateur fluide
- Configuration des routes dédiées pour le système de médias
  - Routes sécurisées avec middleware web
  - Nomenclature cohérente et intuitive
- Création de vues Twig pour l'interface utilisateur
  - Interface moderne pour la liste des médias avec aperçus par type
  - Formulaire d'upload avec support de glisser-déposer via Dropzone.js
  - Page de détails des médias avec affichage adapté au type (images, vidéos, audio, documents)
  - Intégration du lecteur Plyr pour une expérience média optimale
- Harmonisation du système Vibe avec le reste du framework
  - Suivi des conventions de nommage et de structure
  - Documentation complète avec PHPDoc
  - Gestion des exceptions spécifiques au module

### Sécurité

- Renforcement de la sécurité du système de médias
  - Validation rigoureuse des fichiers téléversés
  - Vérification des tailles et types de fichiers autorisés
  - Génération de noms de fichiers sécurisés pour éviter les conflits
  - Protection contre les chemins traversants et autres attaques

### Système de gestion des médias (Vibe)

- Création du système de gestion de médias "Vibe"
  - Implémentation de `MediaManager` pour gérer les fichiers et médias
  - Développement du modèle `Media` pour stocker les métadonnées
  - Création de la commande `vibe:create-table` pour générer la table de base de données
  - Implémentation de méthodes pour l'upload, le stockage et la gestion des fichiers
  - Support pour différents types de médias (image, vidéo, audio, document, etc.)
  - Génération automatique de miniatures pour les images
  - Extraction des métadonnées (EXIF, dimensions, etc.)
- Création de composants pour faciliter l'utilisation des médias
  - `MediaPlayer` pour la lecture des fichiers audio et vidéo
  - `FileUploader` pour l'upload de fichiers avec support de glisser-déposer
  - Intégration avec des bibliothèques JavaScript modernes (Dropzone.js, Plyr, etc.)
- Développement d'une architecture flexible avec plusieurs disques de stockage
  - Support pour le stockage local
  - Configuration préparée pour d'autres providers (S3, etc.)

### Classes HTTP (Request et Response)

- Amélioration complète de la classe `Request` :

  - Ajout de PHPDoc à toutes les méthodes
  - Correction des bugs dans les méthodes existantes (notamment `query()` et `isSecure()`)
  - Ajout de méthodes pour les fichiers uploadés : `file()` et `hasFile()`
  - Ajout de méthodes pour l'analyse des URLs : `url()` et `fullUrl()`
  - Implémentation de méthodes pour vérifier le type de requête : `isMethod()`, `isAjax()`, etc.
  - Support amélioré pour les requêtes JSON
  - Intégration avec la nouvelle classe `Collection`

- Amélioration complète de la classe `Response` :
  - Ajout de PHPDoc à toutes les méthodes
  - Ajout de plusieurs méthodes pour créer des réponses spécifiques : `file()`, `download()`, `status()`
  - Ajout de méthodes pour les codes d'état courants : `notFound()`, `forbidden()`, `unauthorized()`, etc.
  - Amélioration de la méthode `json()` pour accepter des objets
  - Ajout de méthodes pour les messages flash : `withSuccess()`, `withError()`, etc.
  - Ajout de méthodes pour la gestion des en-têtes HTTP

### Support

- Ajout de la classe `Collection` pour manipuler des collections de données :
  - Implémentation des interfaces `ArrayAccess`, `Countable`, `IteratorAggregate` et `JsonSerializable`
  - Méthodes fluides pour toutes les opérations
  - Méthodes pour la manipulation des collections : `filter()`, `map()`, `sort()`, etc.
  - Implémentation propre et complète inspirée des meilleures pratiques

### CraftPanel (Améliorations)

- Ajout de nouveaux middlewares pour renforcer la sécurité du CraftPanel
  - `CraftPanelCsrfMiddleware` pour la protection contre les attaques CSRF
  - `CraftPanelThemeMiddleware` pour la gestion du thème (clair/sombre)
- Amélioration de l'interface utilisateur avec support des thèmes
- Meilleure intégration avec le système d'internationalisation
- Optimisation des performances du tableau de bord
- Renforcement de la sécurité avec validation des formulaires
- Support amélioré pour les permissions granulaires

## [28/03/25]

- Ajout du système d'internationalisation avec Symfony Translation
- Création de la classe Translator dans le namespace Support
- Création du TranslationServiceProvider
- Ajout de la fonction helper trans() pour faciliter l'utilisation des traductions
- Création de la commande make:translation pour générer des fichiers de traduction
- Support pour les formats de traduction PHP, JSON et YAML
- Gestion automatique des dossiers de langue
- Fonction fallback pour revenir à la langue par défaut si une traduction est manquante
- Ajout de nouvelles dépendances dans composer.json
- mise à jour des fichiers composer.lock
- Amélioration des commandes dans le fichier forge.
- Ajout de nouvelles fonctions d'assistance dans helpers.php pour la gestion des sessions et des messages flash.
- Amélioration de la gestion de l'authentification et des contrôleurs OAuth.
- Suppression de l'interface GuardInterface obsolète et mise à jour des gardes d'authentification.
- Refactorisation des modèles et des contrôleurs pour une meilleure structure et fonctionnalité.

### Console/Command

- Mise à jour de la commande `make:scaffold` pour générer automatiquement les composants CRUD
- Implémentation de la génération de modèles avec méthodes de base (find, create, update, delete)
- Création automatique des contrôleurs avec actions CRUD standardisées
- Génération des vues (index, create, edit, show)
- Création automatique des formulaires avec validation intégrée
- Génération des routes RESTful pour chaque ressource
- Création des tests unitaires de base pour chaque composant généré
- Support de la personnalisation des champs via options de ligne de commande
- Intégration avec le système de validation Crucible
- Documentation des méthodes générées avec PHPDoc
- Mise à jour de la commande `make:service`

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
