<?php

declare(strict_types=1);

namespace IronFlow\Channel\Contracts;

use IronFlow\Channel\Events\ChannelEvent;
use IronFlow\Support\Collection;

/**
 * Interface Channel
 * 
 * Définit le contrat de base pour tous les types de channels dans IronFlow.
 * Les channels permettent la communication en temps réel entre le serveur et les clients.
 * 
 * @package IronFlow\Channel\Contracts
 */
interface Channel
{
    /**
     * Renvoie le nom unique du channel
     *
     * @return string Le nom du channel
     */
    public function getName(): string;

    /**
     * Diffuse un événement sur ce channel à tous les abonnés
     *
     * @param ChannelEvent $event L'événement à diffuser
     * @param array $options Options supplémentaires pour la diffusion
     * @return bool True si la diffusion a réussi, false sinon
     * @throws \IronFlow\Channel\Exceptions\BroadcastException Si une erreur survient lors de la diffusion
     */
    public function broadcast(ChannelEvent $event, array $options = []): bool;

    /**
     * Abonne un utilisateur à ce channel
     *
     * @param string $userId ID de l'utilisateur
     * @param array $metadata Métadonnées supplémentaires pour l'abonnement
     * @return bool True si l'abonnement a réussi, false sinon
     * @throws \IronFlow\Channel\Exceptions\SubscriptionException Si l'abonnement échoue
     */
    public function subscribe(string $userId, array $metadata = []): bool;

    /**
     * Désabonne un utilisateur de ce channel
     *
     * @param string $userId ID de l'utilisateur
     * @return bool True si le désabonnement a réussi, false sinon
     */
    public function unsubscribe(string $userId): bool;

    /**
     * Vérifie si un utilisateur est autorisé à s'abonner à ce channel
     *
     * @param string $userId ID de l'utilisateur
     * @param array $metadata Métadonnées supplémentaires pour l'autorisation
     * @return bool True si l'utilisateur est autorisé, false sinon
     */
    public function authorize(string $userId, array $metadata = []): bool;

    /**
     * Renvoie la collection des abonnés de ce channel
     *
     * @return Collection Collection d'abonnés avec leurs métadonnées
     */
    public function getSubscribers(): Collection;

    /**
     * Vérifie si un utilisateur est actuellement abonné au channel
     *
     * @param string $userId ID de l'utilisateur
     * @return bool True si l'utilisateur est abonné, false sinon
     */
    public function hasSubscriber(string $userId): bool;

    /**
     * Récupère les informations d'un abonné spécifique
     *
     * @param string $userId ID de l'utilisateur
     * @return array|null Les informations de l'abonné ou null s'il n'existe pas
     */
    public function getSubscriber(string $userId): ?array;

    /**
     * Définit les options de configuration du channel
     *
     * @param array $options Options de configuration
     * @return self
     */
    public function setOptions(array $options): self;

    /**
     * Récupère les options de configuration actuelles du channel
     *
     * @return array Les options de configuration
     */
    public function getOptions(): array;
}
