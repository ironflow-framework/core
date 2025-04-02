<?php

declare(strict_types=1);

namespace IronFlow\Core\Service;

use IronFlow\Core\Container\ContainerInterface;

/**
 * Classe de base pour les fournisseurs de services
 * 
 * Cette classe abstraite définit l'interface standard pour tous les fournisseurs de services
 * dans l'application. Elle permet d'enregistrer des services dans le conteneur d'injection
 * de dépendances.
 */
abstract class ServiceProvider
{
   /**
    * Le conteneur d'injection de dépendances
    */
   protected ContainerInterface $container;

   /**
    * Crée une nouvelle instance du fournisseur de services
    */
   public function __construct(ContainerInterface $container)
   {
      $this->container = $container;
   }

   /**
    * Enregistre les services dans le conteneur
    * 
    * Cette méthode doit être implémentée par les classes enfants pour définir
    * les services spécifiques qu'elles souhaitent enregistrer.
    */
   abstract public function register(): void;

   /**
    * Initialise les services après leur enregistrement
    * 
    * Cette méthode est appelée après l'enregistrement de tous les services
    * et permet d'effectuer des initialisations supplémentaires si nécessaire.
    */
   public function boot(): void
   {
      // Par défaut, ne fait rien
   }
}
