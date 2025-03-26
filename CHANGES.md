# Historique des Changements

## [26/03/25]

### Database et Iron ORM

- Amélioration de la sécurité et du typage dans la classe Model :
  - Correction des propriétés statiques ($table, $primaryKey)
  - Protection contre les injections SQL avec l'utilisation systématique des requêtes préparées
  - Typage strict des retours de méthodes
  - Utilisation cohérente de la classe Collection pour les résultats multiples
  - Meilleure gestion des erreurs et des cas null
  - Utilisation systématique des principes PDO

## [24/03/25]

### Database et Iron ORM

- Refactoring
- Correction des bugs aux niveaux des relations
- Mise à jour des methodes static CRUD au niveau de la classe __IronFlow\Database\Model__
- Ajout de la classe __Schema__ afin de repartir les tâches au différentes classes

### Gestion des forms

- Création d'un dossier **Forms** pour la gestion des formulaires
- Déplacement des classes déjà créées dans le dossier et restructuration
- Seule les composants sont restés dans Views\Components

## [21/03/25]

### Tests et Documentation

- Ajout des tests unitaires pour les composants :
  - ButtonTest
  - CardTest
  - ContainerTest
  - GridTest
  - ColumnTest
- Création de la documentation détaillée des composants dans docs/components.md
- Ajout d'exemples d'utilisation dans ExampleController

### Database - Iron ORM

- Créer la classe Connection  
- Ajout un système factory  
- Mise à jour Migration, Seeder, Model commandes  
- Révue de la classe IronFlow\Database\Collection  
- Renommage de Schema en Anvil  

## [20/03/25]

### Routage

- Modification de la syntaxe du routage pour utiliser des méthodes statiques
- Standardisation des callbacks de routes vers le format [Controller::class, 'method']
- Ajout d'une méthode statique auth pour les routes d'authentification

### Front-end

- Mise en place du design de la page de bienvenue
- Implémentation de l'affichage des erreurs sur une page dédiée
- Ajout des pages d'erreur par défaut (404, 403, 500) dans le front-end

### Composants et Formulaires

- Création de la structure de base des composants
- Implémentation de la classe Component de base
- Création du trait HasForm pour la gestion des formulaires
- Implémentation de la classe Form de base
- Ajout des composants de formulaire :
  - Classe abstraite Field pour la base des champs
  - Composant Input pour les champs texte
  - Composant Textarea pour les zones de texte
  - Composant Select pour les listes déroulantes
  - Composant Checkbox pour les cases à cocher
  - Composant Radio pour les boutons radio
  - Composant File pour les uploads de fichiers
  - Composant DatePicker pour la sélection de dates
  - Composant ColorPicker pour la sélection de couleurs
- Intégration du design Tailwind CSS pour les composants
- Implémentation du système de validation (Crucible) :
  - Classe Validator pour la validation des données
  - Intégration des règles de validation de base
  - Support des messages d'erreur personnalisés
  - Affichage des erreurs dans les composants de formulaire
  - Support des règles de validation personnalisées
  - Ajout des validateurs spécialisés :
    - FileValidator pour la validation des fichiers
    - DateValidator pour la validation des dates
    - ColorValidator pour la validation des couleurs

### Composants de Mise en Page

- Création de la classe abstraite Layout
- Implémentation des composants de mise en page :
  - Container pour la gestion de la largeur maximale
  - Grid pour la mise en page en colonnes
  - Column pour la gestion des colonnes dans la grille

### Composants UI Réutilisables

- Création des composants UI de base :
  - Card pour les cartes avec support des images, titres, actions et footer
  - Button avec différentes variantes, tailles et support des icônes

## [En cours]

### Nomenclature

- Renommage des composants pour suivre une thématique de forge :
  - CraftPanel : Panel d'administration
  - Iron : ORM
  - Anvil : Système de génération de code (équivalent Blueprint)
  - Forge : CLI et outils de génération
  - Hammer : Système de cache
  - Furnace : Système de traitement des formulaires
  - Crucible : Système de validation
  - Tongs : Système de gestion des fichiers

### Système de Composants

- [x] Création d'une nouvelle structure pour les composants :
  - `src/View/Components/` : Composants de base
  - `src/View/Components/Forms/` : Composants de formulaire
  - `src/View/Components/Layout/` : Composants de mise en page
  - `src/View/Components/UI/` : Composants d'interface utilisateur

### Système de Formulaires (Forge)

- [x] Création de la classe `Form` pour la gestion des formulaires
- [x] Implémentation des composants de base :
  - Input (text, email, password, etc.)
  - Textarea
  - Select
  - Checkbox
  - Radio
  - File (upload de fichiers)
  - DatePicker (sélection de dates)
  - ColorPicker (sélection de couleurs)
- [x] Support des attributs HTML5
- [x] Gestion des erreurs de validation
- [x] Intégration avec Tailwind CSS

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
