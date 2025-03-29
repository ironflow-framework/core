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
    * @var array
    */
   protected array $states = [];

   /**
    * États actifs pour la génération en cours
    *
    * @var array
    */
   protected array $activeStates = [];

   /**
    * Valeurs écrasées pour la génération en cours
    *
    * @var array
    */
   protected array $overrides = [];

   /**
    * Instance de Faker
    *
    * @var Generator
    */
   protected Generator $faker;

   /**
    * Constructeur
    *
    * @param PDO $connection Connexion à la base de données
    * @param string|null $locale Locale pour Faker (default: fr_FR)
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
    * @return array
    */
   abstract public function definition(Generator $faker): array;

   /**
    * Définit le nombre d'instances à créer
    *
    * @param int $count Nombre d'instances
    * @return $this
    */
   public function count(int $count): self
   {
      $this->count = $count;
      return $this;
   }

   /**
    * Retourne une nouvelle instance du modèle avec les attributs générés
    *
    * @param array $overrides Attributs supplémentaires
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
    * @param array $overrides Attributs supplémentaires
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
    * @param array $overrides Attributs supplémentaires
    * @return array
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
    * @return $this
    */
   public function state(string $state): self
   {
      if (!isset($this->states[$state])) {
         throw new \InvalidArgumentException("L'état '{$state}' n'est pas défini.");
      }

      $this->activeStates[] = $state;
      return $this;
   }

   /**
    * Écrase certains attributs pour la génération en cours
    *
    * @param array $overrides Attributs à écraser
    * @return $this
    */
   public function withOverrides(array $overrides): self
   {
      $this->overrides = array_merge($this->overrides, $overrides);
      return $this;
   }

   /**
    * Obtient les attributs complets pour la création du modèle
    *
    * @param array $overrides Attributs supplémentaires
    * @return array
    */
   protected function getAttributes(array $overrides = []): array
   {
      // Commencer avec les attributs par défaut
      $finalAttributes = $this->definition($this->faker);

      // Appliquer les états actifs
      foreach ($this->activeStates as $state) {
         $stateAttributes = $this->states[$state]();
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
    * @return $this
    */
   public function setLocale(string $locale): self
   {
      $this->faker = FakerFactory::create($locale);
      return $this;
   }
}
