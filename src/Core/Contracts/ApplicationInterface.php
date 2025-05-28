<?php

declare(strict_types=1);

namespace IronFlow\Core\Contracts;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Routing\RouterInterface;

interface ApplicationInterface
{
   /**
    * Obtient l'instance unique de l'application
    */
   public static function getInstance(?string $basePath = null): self;

   /**
    * Initialise l'application
    */
   public function bootstrap(): void;

   /**
    * Exécute l'application
    */
   public function run(): void;

   /**
    * Gère une exception
    */
   public function handleException(\Throwable $e): Response;

   /**
    * Obtient le conteneur d'injection de dépendances
    */
   public function getContainer(): ContainerInterface;

   /**
    * Obtient le routeur
    */
   public function getRouter(): RouterInterface;

   /**
    * Enregistre un fournisseur de service
    */
   public function registerServiceProvider(string $provider): void;

   /**
    * Obtient le chemin de base de l'application
    */
   public function getBasePath(): string;
}
