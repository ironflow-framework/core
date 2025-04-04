<?php

declare(strict_types=1);

namespace IronFlow\Database\Factories;

use PDO;
use IronFlow\Database\Model;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use IronFlow\Database\Contracts\FactoryInterface;

/**
 * Classe de base pour les factories de modèles
 * 
 * Cette classe fournit une base pour la génération de données de test
 * pour les modèles de l'application. Elle utilise Faker pour générer
 * des données aléatoires réalistes.
 * 
 * @package IronFlow\Database\Factories
 * @author IronFlow Team
 * @version 1.0.0
 */
abstract class Factory implements FactoryInterface
{
   /**
    * Instance de la connexion à la base de données
    *
    * @var PDO
    */
   protected PDO $connection;

   /**
    * Nom de la classe du modèle associé
    *
    * @var string
    */
   protected string $model;

   /**
    * Nombre d'instances à créer par défaut
    *
    * @var int
    */
   protected int $count = 1;

   /**
    * États disponibles pour la factory
    *
    * @var array<string, callable>
    */
   protected array $states = [];

   /**
    * États actifs pour la génération en cours
    *
    * @var array<string>
    */
   protected array $activeStates = [];

   /**
    * Valeurs écrasées pour la génération en cours
    *
    * @var array<string, mixed>
    */
   protected array $overrides = [];

   /**
    * Instance de Faker pour la génération de données
    *
    * @var Generator
    */
   protected Generator $faker;

   /**
    * Séquence d'attributs incrémentaux
    *
    * @var array<string, mixed>|null
    */
   protected ?array $sequence = null;

   /**
    * Constructeur
    *
    * @param PDO $connection Connexion à la base de données
    * @param string|null $locale Locale pour Faker (default: fr_FR)
    * @throws \InvalidArgumentException Si le modèle n'existe pas
    */
   public function __construct(PDO $connection, ?string $locale = 'fr_FR')
   {
      $this->connection = $connection;
      $this->faker = FakerFactory::create($locale);

      if (!class_exists($this->model)) {
         throw new \InvalidArgumentException("Le modèle '{$this->model}' n'existe pas.");
      }
   }

   /**
    * Définit les attributs par défaut du modèle
    *
    * @param Generator $faker Instance de Faker
    * @return array<string, mixed>
    */
   abstract public function definition(Generator $faker): array;

   /**
    * Définit le nombre d'instances à créer
    *
    * @param int $count Nombre d'instances
    * @return static
    */
   public function count(int $count): static
   {
      $this->count = $count;
      return $this;
   }

   /**
    * Retourne une nouvelle instance du modèle avec les attributs générés
    *
    * @param array<string, mixed> $overrides Attributs supplémentaires
    * @return Model
    */
   public function make(array $overrides = []): Model
   {
      /** @var Model $model */
      $model = new $this->model();
      $model->fill($this->getAttributes($overrides));

      return $model;
   }

   /**
    * Crée et sauvegarde une nouvelle instance du modèle dans la base de données
    *
    * @param array<string, mixed> $overrides Attributs supplémentaires
    * @return Model
    */
   public function create(array $overrides = []): Model
   {
      $model = $this->make($overrides);
      $model->save();

      return $model;
   }

   /**
    * Crée et sauvegarde plusieurs instances du modèle dans la base de données
    *
    * @param array<string, mixed> $overrides Attributs supplémentaires
    * @return array<Model>
    */
   public function createMany(array $overrides = []): array
   {
      $models = [];

      for ($i = 0; $i < $this->count; $i++) {
         $models[] = $this->create($overrides);
      }

      return $models;
   }

   /**
    * Applique un état à la factory
    *
    * @param string $state Nom de l'état
    * @return static
    * @throws \InvalidArgumentException Si l'état n'existe pas
    */
   public function state(string $state): static
   {
      if (!isset($this->states[$state])) {
         throw new \InvalidArgumentException("L'état '{$state}' n'est pas défini.");
      }

      $this->activeStates[] = $state;
      return $this;
   }

   /**
    * Applique plusieurs états à la factory
    *
    * @param array<string> $states Liste des états à appliquer
    * @return static
    */
   public function states(array $states): static
   {
      foreach ($states as $state) {
         $this->state($state);
      }
      return $this;
   }

   /**
    * Écrase certains attributs pour la génération en cours
    *
    * @param array<string, mixed> $overrides Attributs à écraser
    * @return static
    */
   public function withOverrides(array $overrides): static
   {
      $this->overrides = array_merge($this->overrides, $overrides);
      return $this;
   }

   /**
    * Réinitialise les états et les écrasements
    *
    * @return static
    */
   public function reset(): static
   {
      $this->activeStates = [];
      $this->overrides = [];
      return $this;
   }

   /**
    * Obtient les attributs complets pour la création du modèle
    *
    * @param array<string, mixed> $overrides Attributs supplémentaires
    * @return array<string, mixed>
    */
   protected function getAttributes(array $overrides = []): array
   {
      // Commencer avec les attributs par défaut
      $finalAttributes = $this->definition($this->faker);

      // Appliquer les états actifs
      foreach ($this->activeStates as $state) {
         $stateAttributes = $this->states[$state]($this->faker);
         $finalAttributes = array_merge($finalAttributes, $stateAttributes);
      }

      // Appliquer les écrasements globaux
      $finalAttributes = array_merge($finalAttributes, $this->overrides);

      // Appliquer les attributs spécifiques à cette instance
      $finalAttributes = array_merge($finalAttributes, $overrides);

      return $finalAttributes;
   }

   /**
    * Retourne l'instance de Faker
    *
    * @return Generator
    */
   public function getFaker(): Generator
   {
      return $this->faker;
   }

   /**
    * Change la locale de Faker
    *
    * @param string $locale Nouvelle locale
    * @return static
    */
   public function setLocale(string $locale): static
   {
      $this->faker = FakerFactory::create($locale);
      return $this;
   }

   /**
    * Crée une séquence d'instances avec des attributs incrémentaux
    *
    * @param array<string, mixed> $sequence Liste des attributs à incrémenter
    * @return static
    */
   public function sequence(array $sequence): static
   {
      $this->sequence = $sequence;
      return $this;
   }
}
