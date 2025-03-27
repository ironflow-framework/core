# TODO LIST

## POINT A REVOIR SUR IRONFLOW

### Global

[] Utiliser les principes de la PDO  
[] Mettre en place une nomenclature lié à la forge pour certaines fonctionnalités. Ex: CraftPanel pour le panel d'administration, Iron pour l'ORM, Anvril équivalent du 'Blueprint' de laravel  
[] Essayer d'être authentique dans certaines pratique

### Routage

[x] Changer la syntaxe du routage avec l'utilisateur des methodes static pour certaines methodes. Ex: Router::get('/', [HomeController::class, 'index'])->middleware('auth')->name('home');  
[x] Garder uniquement une seule approche pour les callback des routes : [Controller::class, 'method']  
[x] Ajouter une methode static auth pour avoir les routes de l'authentification

### Front-end

[x] Mettre en place le design de la page de bienvenue.  
[x] Afficher les erreurs sur une page d'erreur afin d'être interactif et explicite sur les erreurs
[x] Prévoir un système de composant
[x] Mettre en place des pages 404, 403, 500 par défaut dans le front-end. Ces pages peuvent être overwrite par l'utilisateur du framework

### Gestion des formulaires

[x] Mettre en place un système de formulaire  
[x] Prévoir un trait 'HasForm' ou autre nom pour lier un formulaire à un Model  
[x] Prévoir un design par défaut pour mes champs et composant de formulaire  
[x] Support des messages d'erreur personnalisés
[x] Affichage des erreurs dans les composants de formulaire
[] Prévoir des composants de formulaire telle que login form, contact form, checkout form. Ces composants peuvent être overwrite ou surchargée

### Database

[x] Pour les models avoir la possibilité d'avoir aussi des methodes static pour certaines requete telle que le crud, le find, le findOrfail, etc...  
[x] Corrigé les bugs actuels  
[x] Revoir le système de migration notamment la partie schema  
[x] Completer le système de factorie et de seeding  
[] Completer des choses si possible

### Console

[x] Mettre à jour les commandes manquantes de generation de fichier et code  
[x] Ajouter des commandes  
[x] Afficher les logs en console
[] Revoir la commande MakeModel et MakeForm en adaptant la commande MakeForm aux différents cas : associé à un model ou non

### Cache

[x] Mettre en place le système de cache

### Service

#### Système de paiement

[] Ajouter une commande pour activer ou bien générer le système de paiement pré-configuré (stripe et paypal) ou encore juste installer via composer mais en l'activant au préable

#### Service de mail

[] Configurer le service de mail

### Autres services

[] Ajouter d'autres services si possible

### Formulaire
[] Structuration des fichiers  
[] Gerer la géneration avec ou sans model associé  
[] Factoring  

### CraftPanel
[] Creation des interfaces (vue twig) en respectant la design lié à la forge et plutôt moderne  
[] Génération de la config du craftpanel  
[] Création de la commande d'installation et de configuration  
[] Création de la commande d'ajout d'un model au niveau de craftpanel afin qu'il pris en compte dans l'administartion  
[] Génération des fichier necessaires au fonctionnement du craftpanel  
[] Mise à jour du CraftController afin d'integrer les fonctionnalités de gestion des formulaires et des validations du framework  

### Controller  

[] Cooriger les bugs presents dans la classe Controller

### Refactoring  

[] Repartir les responsabilités  
[] Code facile à maintenir  
[] Système de log

