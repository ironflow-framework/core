# TODO LIST POUR LE FRAMEWORK IRONFLOW

## POINT A REVOIR SUR IRONFLOW

### Global
[x] Pour les commandes cli toujours suivre ce qui fait dans le dossier Console/Commands  
[] Avoir un framework assez sûr  
[] Internalise le framework  

### Internationalisation

[] Utilisé une librairie pour gérer l'internationalisation 

### Le système de Middleware

[] Revoir le système de middleware  

### Application

[] Revoir les fichiers du dossier Application
[] Revoir le container de service  
[] Revoir aussi le dossier Providers

### Database

[] Completer le système de factorie et de seeding  
[] Completer des choses si possible

### Système de component

[] Reorganise le système de composant  
[] Ajoute des composants  
[] Mets à jour les components existants (Par défaut, le style des components sera proche de celui de la forge comme le style du framework. L'utilisateur pourra bien sûr modifie le style s'il veut)  

### Système de gestion des uploads et média

[] Mettre en place un système de gestion des uploads et media appelé 'Vibe'  
[] Mettre en place des composants de lecture des médias audio et vidéo  
[] Mettre en place aussi un système d'upload des fichiers 
[] Harmoniser le système avec ce qui est déjà present dans le framework  

### Console

[] Revoir la commande MakeModel et MakeForm en adaptant la commande MakeForm aux différents cas : associé à un model ou non  
[] Analyser toutes les commandes du dossier Console/Commands et mettre à jour les commandes qui necessite des ajustements et/ou des mises à jour
[] Ajouter des commandes  
[] Creer une commande l'installateur interactive comme avec Next ou Adonis afin de permettre à l'utilisateur de déjà choisir certaines config telle que :
   - le nom de l'app
   - si le projet est web ou api
   - le driver de db, s'il veut utiliser le système d'auth du framework et si oui lequel (guard, session, token)
   - le système de cache  
   - s'il veut utiliser le craftpanel  
   - Et autres questions
   Cela d'avoir une base dès le depart  


### Système d'authentification  

[] Revoir la partie command cli et l'adapte sous le forme des autres commandes du dossier Console/Commands   

### Cache

[] Mettre à jour ou revoir entierement le système de cache  
[] Tester le système de cache  
[] Tester si le système de cache permet au framework d'être plus rapide et performant      

### CraftPanel
[] Creation des interfaces (vue twig) en respectant la design lié à la forge et plutôt moderne avec thème sombre et clair  
[] Génération de la config du craftpanel  
[] Création de la commande d'installation et de configuration  
[] Création de la commande d'ajout d'un model au niveau de craftpanel afin qu'il pris en compte dans l'administartion  
[] Génération des fichier necessaires au fonctionnement du craftpanel  
[] Mise à jour du CraftController afin d'integrer les fonctionnalités de gestion des formulaires et des validations du framework  
[] Utiliser l'internationalisation  

### Controller  
[] Cooriger les bugs presents dans la classe Controller

### Classe Request et Response 
[] Mettre à jour ces classes en ajoutant des methodes utiles  

### Refactoring  
[] Bonne pratique et harmonisation en respectant la modularité    
[] Repartir les responsabilités  
[] Code facile à maintenir  
[] Système de log
[] Avoir un système scalable et sécurisé   

### Services

#### Système de paiement
[] Ajouter une commande pour activer ou bien générer le système de paiement pré-configuré (stripe et paypal) ou encore juste installer via composer mais en l'activant au préable

#### Service de mail
[] Configurer le service de mail

#### Autres services
[] Ajouter d'autres services si possible
 

