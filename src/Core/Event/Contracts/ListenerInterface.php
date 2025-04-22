<?php

declare(strict_types=1);

namespace IronFlow\Core\Event\Contracts;

/**
 * Interface pour les écouteurs d'événements
 */
interface ListenerInterface
{
   /**
    * Récupère la priorité de l'écouteur
    *
    * @return int
    */
   public function getPriority(): int;

   /**
    * Traite l'événement
    *
    * @param EventInterface $event
    * @param array $payload
    * @return mixed
    */
   public function handle(EventInterface $event, array $payload = []): mixed;
}
