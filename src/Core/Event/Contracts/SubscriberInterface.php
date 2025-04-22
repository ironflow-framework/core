<?php

declare(strict_types=1);

namespace IronFlow\Core\Event\Contracts;

/**
 * Interface pour les abonnés aux événements
 */
interface SubscriberInterface
{
   /**
    * Récupère les événements auxquels l'abonné est souscrit
    *
    * @return array<string, string|array<string>>
    */
   public function getSubscribedEvents(): array;
}
