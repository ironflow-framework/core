<?php

declare(strict_types=1);

namespace IronFlow\Channel\Console\Commands;

use IronFlow\Console\Command;
use IronFlow\Channel\Server\WebSocketServer;
use IronFlow\Support\Facades\Config;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour démarrer le serveur WebSocket
 */
class WebSocketServerCommand extends Command
{
    /**
     * Configuration de la commande
     */
    protected function configure(): void
    {
        $this->setName('websocket:serve')
            ->setDescription('Démarre le serveur WebSocket')
            ->setHelp('Cette commande démarre le serveur WebSocket pour la communication en temps réel');
    }

    /**
     * Exécution de la commande
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = Config::get('channel.providers.websocket', []);
        $host = $config['host'] ?? '0.0.0.0';
        $port = $config['port'] ?? 8080;

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    new WebSocketServer()
                )
            ),
            $port,
            $host
        );

        $output->writeln("<info>Serveur WebSocket démarré sur {$host}:{$port}</info>");
        $server->run();

        return Command::SUCCESS;
    }
}
