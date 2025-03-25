# Composants de Mise en Page et UI

Ce document décrit les composants de mise en page et UI disponibles dans le framework IronFlow.

## Composants de Mise en Page

### Container

Le composant `Container` permet de créer un conteneur responsive avec une largeur maximale.

```php
use IronFlow\View\Components\Layout\Container;

$container = new Container();
$container->fluid(); // Optionnel : conteneur fluide
$container->maxWidth('5xl'); // Optionnel : largeur maximale personnalisée
$container->setContent('Contenu du conteneur');
```

### Grid

Le composant `Grid` crée une grille responsive basée sur Tailwind CSS.

```php
use IronFlow\View\Components\Layout\Grid;

$grid = new Grid();
$grid->columns(12); // Nombre de colonnes (par défaut : 12)
$grid->gap('4'); // Espacement entre les colonnes
$grid->breakpoints(['sm', 'md', 'lg']); // Points de rupture personnalisés
$grid->setContent('Contenu de la grille');
```

### Column

Le composant `Column` définit une colonne dans une grille.

```php
use IronFlow\View\Components\Layout\Column;

$column = new Column();
$column->span(6); // Largeur de la colonne
$column->offset(3); // Décalage de la colonne
$column->order(2); // Ordre d'affichage
$column->setContent('Contenu de la colonne');
```

## Composants UI

### Card

Le composant `Card` crée une carte avec titre, sous-titre, image et actions.

```php
use IronFlow\View\Components\UI\Card;

$card = new Card();
$card->title('Titre de la carte')
     ->subtitle('Sous-titre')
     ->image('chemin/vers/image.jpg')
     ->addAction('Voir plus', '/details')
     ->hover() // Effet de survol
     ->shadow() // Ombre portée
     ->padding('p-6'); // Padding personnalisé
$card->setContent('Contenu de la carte');
```

### Button

Le composant `Button` crée un bouton stylisé avec différentes variantes.

```php
use IronFlow\View\Components\UI\Button;

$button = new Button();
$button->type('button') // type, submit, reset
        ->variant('primary') // primary, secondary, success, danger, warning, info
        ->size('md') // sm, md, lg
        ->fullWidth() // Bouton pleine largeur
        ->disabled() // Bouton désactivé
        ->icon('plus') // Icône
        ->iconOnly(); // Bouton avec icône uniquement
$button->setContent('Texte du bouton');
```

## Exemple d'Utilisation

Voici un exemple complet utilisant plusieurs composants :

```php
use IronFlow\View\Components\Layout\Container;
use IronFlow\View\Components\Layout\Grid;
use IronFlow\View\Components\Layout\Column;
use IronFlow\View\Components\UI\Card;
use IronFlow\View\Components\UI\Button;

// Création du conteneur
$container = new Container();

// Création de la grille
$grid = new Grid();
$grid->gap(['4', '6', '8']);

// Création des colonnes avec des cartes
$column1 = new Column();
$column1->span(['12', '6', '4']);

$card1 = new Card();
$card1->title('Carte 1')
      ->subtitle('Sous-titre')
      ->image('image1.jpg')
      ->addAction('Voir plus', '/details/1')
      ->hover();

$column1->setContent($card1->render());

// Ajout des colonnes à la grille
$grid->setContent($column1->render());

// Ajout de la grille au conteneur
$container->setContent($grid->render());

// Ajout d'un bouton d'action
$button = new Button();
$button->type('button')
        ->variant('primary')
        ->size('lg')
        ->icon('plus')
        ->setContent('Ajouter');

$container->setContent($container->getContent() . $button->render());

// Rendu final
echo $container->render();
```

## Personnalisation

Les composants peuvent être personnalisés en utilisant les méthodes de chaînage disponibles. Chaque composant accepte également des attributs HTML personnalisés via la méthode `withAttributes()`.

## Tests

Les composants sont accompagnés de tests unitaires qui vérifient leur bon fonctionnement. Les tests couvrent :

- Le rendu avec les attributs par défaut
- Les différentes variantes et options
- La personnalisation des attributs
- Les comportements responsifs
- Les interactions utilisateur (hover, disabled, etc.)
