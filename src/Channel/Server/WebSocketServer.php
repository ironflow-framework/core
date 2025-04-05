<?php

declare(strict_types=1);

namespace IronFlow\Channel\Server;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use IronFlow\Support\Collection;
use IronFlow\Support\Facades\Log;
use SplObjectStorage;

/**
 * Serveur WebSocket pour IronFlow
 * 
 * Gère les connexions WebSocket et la diffusion des messages
 */
class WebSocketServer implements MessageComponentInterface
{
    /**
     * Connexions actives
     */
    protected Collection $clients;

    /**
     * Map des identifiants de connexion
     */
    protected SplObjectStorage $connectionIds;

    /**
     * Compteur pour les identifiants de connexion
     */
    protected int $connectionCounter = 0;

    /**
     * Abonnements aux channels
     */
    protected array $subscriptions = [];

    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->clients = new Collection();
        $this->connectionIds = new SplObjectStorage();
    }

    /**
     * Génère un identifiant unique pour une connexion
     */
    protected function generateConnectionId(): string
    {
        return (string)++$this->connectionCounter;
    }

    /**
     * Récupère l'identifiant d'une connexion
     */
    protected function getConnectionId(ConnectionInterface $conn): string
    {
        if (!$this->connectionIds->contains($conn)) {
            $this->connectionIds[$conn] = $this->generateConnectionId();
        }
        return (string)$this->connectionIds[$conn];
    }

    /**
     * Gère une nouvelle connexion
     */
    public function onOpen(ConnectionInterface $conn): void
    {
        $connectionId = $this->getConnectionId($conn);
        $this->clients->offsetSet($connectionId, $conn);
        Log::info("Nouvelle connexion! ({$connectionId})");
    }

    /**
     * Gère la réception d'un message
     */
    public function onMessage(ConnectionInterface $from, $msg): void
    {
        try {
            $data = json_decode($msg, true);
            if (!$data || !isset($data['action'])) {
                return;
            }

            switch ($data['action']) {
                case 'subscribe':
                    $this->handleSubscribe($from, $data);
                    break;
                case 'unsubscribe':
                    $this->handleUnsubscribe($from, $data);
                    break;
                case 'broadcast':
                    $this->handleBroadcast($from, $data);
                    break;
            }
        } catch (\Throwable $e) {
            Log::error("Erreur de traitement du message: " . $e->getMessage());
        }
    }

    /**
     * Gère la fermeture d'une connexion
     */
    public function onClose(ConnectionInterface $conn): void
    {
        $connectionId = $this->getConnectionId($conn);
        $this->clients->offsetUnset($connectionId);
        $this->removeFromAllChannels($conn);
        $this->connectionIds->detach($conn);
        Log::info("Connexion {$connectionId} fermée");
    }

    /**
     * Gère une erreur de connexion
     */
    public function onError(ConnectionInterface $conn, \Exception $e): void
    {
        Log::error("Erreur pour la connexion {$this->getConnectionId($conn)}: {$e->getMessage()}");
        $conn->close();
    }

    /**
     * Gère l'abonnement à un channel
     */
    protected function handleSubscribe(ConnectionInterface $conn, array $data): void
    {
        if (!isset($data['channel'])) {
            return;
        }

        $channel = $data['channel'];
        $connectionId = $this->getConnectionId($conn);

        if (!isset($this->subscriptions[$channel])) {
            $this->subscriptions[$channel] = new Collection();
        }

        $this->subscriptions[$channel]->offsetSet($connectionId, $conn);
        Log::info("Client {$connectionId} abonné au channel {$channel}");

        $response = [
            'event' => 'subscribed',
            'channel' => $channel
        ];
        $conn->send(json_encode($response));
    }

    /**
     * Gère le désabonnement d'un channel
     */
    protected function handleUnsubscribe(ConnectionInterface $conn, array $data): void
    {
        if (!isset($data['channel'])) {
            return;
        }

        $channel = $data['channel'];
        $connectionId = $this->getConnectionId($conn);

        if (isset($this->subscriptions[$channel])) {
            $this->subscriptions[$channel]->offsetUnset($connectionId);
            Log::info("Client {$connectionId} désabonné du channel {$channel}");
        }
    }

    /**
     * Gère la diffusion d'un message
     */
    protected function handleBroadcast(ConnectionInterface $from, array $data): void
    {
        if (!isset($data['channel'], $data['event'])) {
            return;
        }

        $channel = $data['channel'];
        if (!isset($this->subscriptions[$channel])) {
            return;
        }

        $message = json_encode([
            'channel' => $channel,
            'event' => $data['event'],
            'data' => $data['data'] ?? null
        ]);

        $fromId = $this->getConnectionId($from);
        foreach ($this->subscriptions[$channel] as $connectionId => $client) {
            if ($connectionId !== $fromId) {
                $client->send($message);
            }
        }
    }

    /**
     * Supprime un client de tous les channels
     */
    protected function removeFromAllChannels(ConnectionInterface $conn): void
    {
        $connectionId = $this->getConnectionId($conn);
        foreach ($this->subscriptions as $channel => $clients) {
            $clients->offsetUnset($connectionId);
            if ($clients->count() === 0) {
                unset($this->subscriptions[$channel]);
            }
        }
    }
}
