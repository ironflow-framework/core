<?php

declare(strict_types=1);

namespace IronFlow\Core\Service\Contracts;

/**
 * Interface pour les services d'application
 * 
 * Cette interface définit les méthodes que tous les services doivent implémenter.
 */
interface ServiceInterface
{
   /**
    * Enregistre le service dans l'application
    *
    * @return void
    */
   public function register(): void;

   /**
    * Démarre le service après son enregistrement
    *
    * @return void
    */
   public function boot(): void;
}
