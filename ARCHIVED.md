# FONCTIONNALITE ANCIENNEMENT DANS LA TODO.md REALISEES

- [Done]: Tâches réalisées en globalité et est considéré comme archivée
- [In Progress]: Tâches toujours en cours de réalisation car parfois dépendant des nouvelles fonctionnalités pour être finaliser
- [PARTIAL Done]: Tâches réalisée en partie ou doit être revue

## ARCHIVER ET REALISER - MARS 2025

### Sécurité [Done]

[x] Avoir un framework assez sécurité, si le framework n'est pas sécurisé le sécurisé
[x] Audit complet et correction des vulnérabilités
[x] Implémentation de mécanismes de protection avancés

### Classe Request et Response [Done]

[x] Mettre à jour ces classes en ajoutant des methodes utiles

### Système de gestion des uploads et média [Done]

[x] Mettre en place un système de gestion des uploads et media appelé 'Vibe'  
[x] Mettre en place des composants de lecture des médias audio et vidéo  
[x] Mettre en place aussi un système d'upload des fichiers
[x] Harmoniser le système avec ce qui est déjà present dans le framework

### Système de component [Done]

[x] Reorganise le système de composant  
[x] Ajoute des composants
[x] Mets à jour les components existants (style cohérent avec la forge)

### Controller [Done]

[x] Corriger les bugs présents dans la classe Controller

### Console et CLI [Done]

[x] Revoir la commande MakeModel et MakeForm en adaptant la commande MakeForm aux différents cas
[x] Revoir la partie command CLI d'authentification et l'adapter à la forme des autres commandes
[x] Harmoniser la structure des commandes dans le dossier Console/Commands

### Refactoring [Partial Done]

[x] Bonne pratique et harmonisation en respectant la modularité  
[x] Répartir les responsabilités

### Application [Partial Done]

[x] Revoir le container de service

## ARCHIVER ET REALISER

### Routage [Done]

[x] Changer la syntaxe du routage avec l'utilisateur des methodes static pour certaines methodes. Ex: Router::get('/', [HomeController::class, 'index'])->middleware('auth')->name('home');  
[x] Garder uniquement une seule approche pour les callback des routes : [Controller::class, 'method']  
[x] Ajouter une methode static auth pour avoir les routes de l'authentification

### Front-end [Done]

[x] Mettre en place le design de la page de bienvenue.  
[x] Afficher les erreurs sur une page d'erreur afin d'être interactif et explicite sur les erreurs
[x] Prévoir un système de composant
[x] Mettre en place des pages 404, 403, 500 par défaut dans le front-end. Ces pages peuvent être overwrite par l'utilisateur du framework
[x] Integrer vite et tailwind css version 4 au framework  
[x] Mettre en place le buider d'asset et le reload des pages

### Global [In Progress]

[x] Utiliser les principes de la POO  
[x] Harmoniser le système complet  
[x] Mettre en place une nomenclature lié à la forge pour certaines fonctionnalités. Ex: CraftPanel pour le panel d'administration, Iron pour l'ORM, Anvril équivalent du 'Blueprint' de laravel, Hammer pour le système de cache  
[x] Essayer d'être authentique dans certaines pratique et fonctionnalité

### Console [In Progress]

[x] Mettre à jour les commandes manquantes de generation de fichier et code  
[x] Ajouter des commandes  
[x] Afficher les logs en console

### Gestion des formulaires [Done]

[x] Mettre en place un système de formulaire  
[x] Prévoir un trait 'HasForm' ou autre nom pour lier un formulaire à un Model  
[x] Prévoir un design par défaut pour mes champs et composant de formulaire  
[x] Support des messages d'erreur personnalisés
[x] Affichage des erreurs dans les composants de formulaire
[x] Prévoir des composants de formulaire telle que login form, contact form, checkout form. Ces composants peuvent être overwrite ou surchargée pour permettre une personnalité de l'utilisateur  
[x] Structuration des fichiers  
[x] Gerer la géneration avec ou sans model associé  
[x] Refactoring

### Database [Partial Done]

[x] Pour les models avoir la possibilité d'avoir aussi des methodes static pour certaines requete telle que le crud, le find, le findOrfail, etc...  
[x] Corrigé les bugs actuels  
[x] Revoir le système de migration notamment la partie schema

### Système d'authentification [Done]

[x] Mettre en place un système d'authentification moderne et sécurisé  
[x] Prévoir une authentication via guard, session, token  
[x] L'utilisation d'une authentication OAuth sera bien donc si possible on pourra utilisé des library existant si la mise en place est complexe  
[x] Prevoir un système de changement de mot de passe et autres points pour l'authentification  
[x] Prevoir un système d'email d'activation  
[x] Prevoir une commande pour initialiser ou installer le système d'authentication  
[x] Mettre en place des middlewares  
[x] Prevoir une commande pour 'intialiser' ou 'activer' ou encore 'installer' l'authentification proposer par le framework  
[x] Le système d'authentification devra utiliser les fonctionnalités déjà présentes notamment le système de formulaire, models, composants et autres  
[x] Dans le cas du CraftPanel l'authentification devra aussi tenir compte du système de permission et groupe

### Cache [Done]

[x] Mettre à jour ou revoir entierement le système de cache  
[x] Tester le système de cache  
[x] Tester si le système de cache permet au framework d'être plus rapide et performant

### Internationalisation [Done]

[x] Utilisé une librairie pour gérer l'internationalisation

### Le système de Middleware [Done]

[x] Revoir le système de middleware

### CraftPanel [Done]

[x] Creation des interfaces (vue twig) en respectant la design lié à la forge et plutôt moderne avec thème sombre et clair  
[x] Génération de la config du craftpanel  
[x] Création de la commande d'installation et de configuration  
[x] Création de la commande d'enregistrement d'un model au niveau de craftpanel afin qu'il pris en compte dans l'administration un peu comme avec django  
[x] Génération des fichier necessaires au fonctionnement du craftpanel  
[x] Sécuriser et ajouter des middlewares
[x] Mise à jour du CraftController afin d'integrer les fonctionnalités de gestion des formulaires et des validations du framework  
[x] Le craftpanel pourra aussi tenir compte des rôles et permissions  
[x] Générer des commandes pour initialiser le craftpanel  
[x] Utiliser l'authentification du framework  
[x] Utiliser le système de middleware du framework  
[x] Utiliser le système de composant du framework  
[x] Utiliser le système de formulaire du framework  
[x] Utiliser le système de validation du framework  
[x] Utiliser le système de service du framework  
[x] Ajouter les routes pour accéder au craftpanel
[x] Utiliser l'internationalisation
