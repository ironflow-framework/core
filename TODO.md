# TODO LIST POUR LE FRAMEWORK IRONFLOW

## POINT A REVOIR SUR IRONFLOW

### Refactoring et déplacement de code

[] Refactoriser le code  
[] Separer les responsabilités  
[] Avoir un code maintenable et scalable  
[] Documentation PHPDoc  
[] Avoir un framework leger, modulaire, scalable, moderne et puissant  
[] Stabiliser une version 2 pour mise en production  
[] Proposition amelioration pour la version 3 (Il se peut que la version 3 soit à 60% axé sur la partie front)
[] Preparer une librairy de composant  

### Global

[x] Pour les commandes cli toujours suivre ce qui fait dans le dossier Console/Commands  
[x] Avoir un framework assez sécurité, si le framework n'est pas sécurisé le sécurisé  
[] Internaliser le framework en integrant le multi language (tu peux ajouter des dependances si possible)

### Channel système

[x] Mettre en place un système de channel  

### Craftpanel

- Mise à jour complète du craftpanel  

### Internationalisation

[x] Utilisé une librairie pour gérer l'internationalisation

### Le système de Middleware

[x] Revoir le système de middleware

### Application

[x] Revoir le container de service  
[] Revoir aussi le dossier Providers

### Database

[] Completer le système de factorie et de seeding  
[] Mettre en place une gestion de la base de donnée plus facile et sûre  
[] Ajouter des fonctionnalités pour la gestion de la base de donnée  

### Système de component

[x] Reorganise le système de composant  
[x] Ajoute des composants  
[x] Mets à jour les components existants (Par défaut, le style des components sera proche de celui de la forge comme le style du framework. L'utilisateur pourra bien sûr modifie le style s'il veut)

### Système de gestion des uploads et média

[x] Mettre en place un système de gestion des uploads et media appelé 'Vibe'  
[x] Mettre en place des composants de lecture des médias audio et vidéo  
[x] Mettre en place aussi un système d'upload des fichiers
[x] Harmoniser le système avec ce qui est déjà present dans le framework

### Console

[x] Revoir la commande MakeModel et MakeForm en adaptant la commande MakeForm aux différents cas : associé à un model ou non  
[] Analyser toutes les commandes du dossier Console/Commands et mettre à jour les commandes qui necessite des ajustements et/ou des mises à jour
[x] Ajouter des commandes  
[] Creer une commande l'installateur interactive comme avec Next ou Adonis afin de permettre à l'utilisateur de déjà choisir certaines config telle que :

- le nom de l'app
- si le projet est web ou api
- le driver de db, s'il veut utiliser le système d'auth du framework et si oui lequel (guard, session, token)
- le système de cache
- s'il veut utiliser le craftpanel
- Et autres questions
  Cela d'avoir une base dès le depart

### Système d'authentification

[x] Revoir la partie command cli et l'adapte sous le forme des autres commandes du dossier Console/Commands

### CraftPanel

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

### Refactoring

[x] Bonne pratique et harmonisation en respectant la modularité  
[x] Repartir les responsabilités  
[] Code facile à maintenir  
[] Système de log
[] Avoir un système scalable et sécurisé

### Services

#### Système de paiement

[] Ajouter une commande pour activer ou bien générer le système de paiement pré-configuré (stripe, paypal et autres) ou encore juste installer via composer mais en l'activant au préable

#### Service de mail

[] Configurer le service de mail

#### Système de channel

[] Mettre en place un système de channel  
[] Harmoniser avec le système présent avec le framework

### Integration une IA

[] Mettre en place un système d'integration IA
[] Integrer claude et chatgpt

#### Autres services

[] Ajouter d'autres services telle que celle lié au e-commerce et à la création des articles de blogs

### Gestion des formulaires et système de composant
[] Revoir tout le système en entier  

### Exemple cas d'usage dans un projet du framework

[] Créer une application de démonstration complète
[] Développer un blog avec authentification et gestion des médias
[] Créer une API RESTful sécurisée pour une application mobile
[] Développer un système de e-commerce avec panier et paiement
[] Créer une application de gestion de tâches avec drag-and-drop
[] Mettre en place une documentation détaillée avec exemples
