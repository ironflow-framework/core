<?php

declare(strict_types=1);

namespace IronFlow\Channel\Providers;

use IronFlow\Channel\Contracts\Channel;
use IronFlow\Channel\Contracts\ChannelProvider;
use IronFlow\Channel\Events\ChannelEvent;
use IronFlow\Channel\Models\WebSocketChannel;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Log;
use Ratchet\Client\WebSocket;
use Ratchet\Client\Connector as ClientConnector;
use React\EventLoop\Loop;
use React\Promise\PromiseInterface;
use Throwable;

/**
 * Provider WebSocket pour les channels utilisant Ratchet
 * 
 * Ce provider implémente une connexion WebSocket bidirectionnelle
 * en utilisant la bibliothèque Ratchet.
 */
class WebSocketProvider implements ChannelProvider
{
    /**
     * Configuration du provider
     */
    protected array $config;

    /**
     * Connexion WebSocket active
     */
    protected ?WebSocket $connection = null;

    /**
     * État de la connexion
     */
    protected bool $connected = false;

    /**
     * Event loop pour les opérations asynchrones
     */
    protected $loop;

    /**
     * Liste des channels créés
     */
    protected array $channels = [];

    /**
     * File d'attente des messages pendant la reconnexion
     */
    protected array $messageQueue = [];

    /**
     * Nombre de tentatives de reconnexion
     */
    protected int $reconnectAttempts = 0;

    /**
     * Constructeur
     *
     * @param array $config Configuration du provider
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'host' => '127.0.0.1',
            'port' => 8080,
            'secure' => false,
            'path' => '/',
            'max_reconnect_attempts' => 5,
            'reconnect_interval' => 1000,
        ], $config);

        $this->loop = Loop::get();
    }

    public static function getInstance(): object
    {
        return new self(Config::get('channel'));
    }

    /**
     * {@inheritdoc}
     */
    public function createChannel(string $name, array $options = []): Channel
    {
        if (isset($this->channels[$name])) {
            return $this->channels[$name];
        }

        $channel = new WebSocketChannel($name, array_merge($this->config, $options));
        $this->channels[$name] = $channel;

        if ($this->isConnected()) {
            $this->subscribeChannelToServer($channel);
        }

        return $channel;
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): bool
    {
        if ($this->isConnected()) {
            return true;
        }

        try {
            $scheme = $this->config['secure'] ? 'wss' : 'ws';
            $uri = "{$scheme}://{$this->config['host']}:{$this->config['port']}{$this->config['path']}";

            $connector = new ClientConnector($this->loop);
            
            $connector($uri)
                ->then(
                    function (WebSocket $conn) {
                        $this->connection = $conn;
                        $this->connected = true;
                        $this->onConnect();

                        $conn->on('message', function ($msg) {
                            $this->onMessage($msg);
                        });

                        $conn->on('close', function () {
                            $this->connected = false;
                            $this->onDisconnect();
                        });

                        $conn->on('error', function (Throwable $e) {
                            $this->connected = false;
                            $this->onError($e);
                        });
                    },
                    function (Throwable $e) {
                        $this->connected = false;
                        Log::error("Erreur de connexion WebSocket: " . $e->getMessage());
                        $this->handleReconnect();
                    }
                );

            return true;
        } catch (Throwable $e) {
            $this->connected = false;
            Log::error("Exception WebSocket: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): bool
    {
        if (!$this->isConnected()) {
            return true;
        }

        try {
            $this->connection->close();
            $this->connection = null;
            $this->connected = false;
            $this->reconnectAttempts = 0;
            return true;
        } catch (Throwable $e) {
            Log::error("Erreur de déconnexion WebSocket: " . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected(): bool
    {
        return $this->connection !== null && $this->connected;
    }

    /**
     * Gère la reconnexion automatique
     */
    protected function handleReconnect(): void
    {
        if ($this->reconnectAttempts >= $this->config['max_reconnect_attempts']) {
            Log::error("Nombre maximum de tentatives de reconnexion atteint");
            return;
        }

        $this->reconnectAttempts++;
        $interval = $this->config['reconnect_interval'] * $this->reconnectAttempts;

        Log::info("Tentative de reconnexion dans {$interval}ms...");
        $this->loop->addTimer($interval / 1000, function () {
            $this->connect();
        });
    }

    /**
     * Appelé lors de la connexion réussie
     */
    protected function onConnect(): void
    {
        $this->reconnectAttempts = 0;
        Log::info("Connexion WebSocket établie");

        // Réabonne tous les channels existants
        foreach ($this->channels as $channel) {
            $this->subscribeChannelToServer($channel);
        }

        // Envoie les messages en file d'attente
        foreach ($this->messageQueue as $message) {
            $this->connection->send(json_encode($message));
        }
        $this->messageQueue = [];
    }

    /**
     * Appelé lors de la réception d'un message
     */
    protected function onMessage($msg): void
    {
        try {
            $data = json_decode($msg, true);
            if (!$data || !isset($data['channel'], $data['event'])) {
                return;
            }

            $channel = $this->channels[$data['channel']] ?? null;
            if ($channel) {
                $channel->handleServerEvent($data['event'], $data['data'] ?? []);
            }
        } catch (Throwable $e) {
            Log::error("Erreur de traitement du message WebSocket: " . $e->getMessage());
        }
    }

    /**
     * Appelé lors de la déconnexion
     */
    protected function onDisconnect(): void
    {
        Log::info("Connexion WebSocket fermée");
        $this->connection = null;
        $this->handleReconnect();
    }

    /**
     * Appelé lors d'une erreur
     */
    protected function onError(Throwable $e): void
    {
        Log::error("Erreur WebSocket: " . $e->getMessage());
    }

    /**
     * Abonne un channel au serveur
     */
    protected function subscribeChannelToServer(WebSocketChannel $channel): void
    {
        $message = [
            'action' => 'subscribe',
            'channel' => $channel->getName()
        ];

        if ($this->isConnected()) {
            $this->connection->send(json_encode($message));
        } else {
            $this->messageQueue[] = $message;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function broadcast(string $channelName, ChannelEvent $event): bool
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        if (!isset($this->channels[$channelName])) {
            $this->createChannel($channelName);
        }

        return $this->channels[$channelName]->broadcast($event);
    }
}
