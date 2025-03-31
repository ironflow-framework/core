<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands\Channel;


use IronFlow\Support\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;

/**
 * Commande pour initialiser le système de channel
 */
class ChannelInitCommand extends Command
{
   /**
    * Commande
    */
   protected static $defaultName = 'channel:init';

   /**
    * Description de la commande
    */
   protected static $defaultDescription = 'Initialise le système de channel du framework';

   /**
    * Configure la commande
    */
   protected function configure(): void
   {
      $this->setHelp('Cette commande initialise le système de channel avec les providers et les configurations nécessaires.');
   }

   /**
    * Exécute la commande
    */
   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $io->title('Initialisation du système de channel');

      // Vérifier si le fichier de configuration existe
      if (!file_exists(config_path('channel.php'))) {
         $this->createConfigFile($io);
      } else {
         $io->note('Le fichier de configuration channel.php existe déjà.');
      }

      // Ajouter le ServiceProvider
      $this->registerServiceProvider($io);

      // Publier les dépendances JS
      $this->publishJsDependencies($io);

      // Mettre à jour le fichier .env
      $this->updateEnvFile($io);

      $io->success('Le système de channel a été initialisé avec succès !');
      $io->note([
         'Pour utiliser le système de channel, assurez-vous d\'inclure le script JavaScript dans vos vues.',
         'Exemple: <script src="/js/channel.js"></script>',
         'Puis, dans votre code JavaScript:',
         'const channel = new IronFlowChannel();',
         'channel.subscribe("nom-du-channel", (event) => { console.log(event); });'
      ]);

      return Command::SUCCESS;
   }

   /**
    * Crée le fichier de configuration
    */
   private function createConfigFile(SymfonyStyle $io): void
   {
      $io->section('Création du fichier de configuration');

      $provider = $io->choice(
         'Quel provider de channel souhaitez-vous utiliser par défaut ?',
         ['websocket' => 'WebSocket (intégré)', 'pusher' => 'Pusher', 'socketio' => 'Socket.IO'],
         'websocket'
      );

      $configContent = $this->getConfigTemplate($provider);
      Filesystem::put(config_path('channel.php'), $configContent);

      $io->success('Fichier de configuration channel.php créé avec succès !');
   }

   /**
    * Retourne le template de configuration
    */
   private function getConfigTemplate(string $defaultProvider): string
   {
      return <<<PHP
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuration du système de channel
    |--------------------------------------------------------------------------
    |
    | Cette configuration définit les paramètres du système de channel d'IronFlow,
    | notamment le provider par défaut et les options des différents providers.
    |
    */

    // Provider par défaut
    'default' => env('CHANNEL_PROVIDER', '{$defaultProvider}'),

    // Configuration des providers
    'providers' => [
        'websocket' => [
            'host' => env('WEBSOCKET_HOST', '127.0.0.1'),
            'port' => env('WEBSOCKET_PORT', 8080),
            'path' => env('WEBSOCKET_PATH', '/socket'),
            'secure' => env('WEBSOCKET_SECURE', false),
            'timeout' => env('WEBSOCKET_TIMEOUT', 30),
        ],
        
        'pusher' => [
            'app_id' => env('PUSHER_APP_ID'),
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'cluster' => env('PUSHER_APP_CLUSTER', 'eu'),
            'encrypted' => env('PUSHER_APP_ENCRYPTED', true),
        ],
        
        'socketio' => [
            'host' => env('SOCKETIO_HOST', '127.0.0.1'),
            'port' => env('SOCKETIO_PORT', 6001),
            'path' => env('SOCKETIO_PATH', '/socket.io'),
            'namespace' => env('SOCKETIO_NAMESPACE', '/'),
        ],
    ],

    // Durée de vie des messages en cache (secondes)
    'cache_ttl' => env('CHANNEL_CACHE_TTL', 3600),
    
    // Taille maximale des payloads (octets)
    'max_payload_size' => env('CHANNEL_MAX_PAYLOAD_SIZE', 10240),
    
    // Authentification
    'auth' => [
        'enabled' => env('CHANNEL_AUTH_ENABLED', true),
        'route' => env('CHANNEL_AUTH_ROUTE', '/broadcasting/auth'),
    ],
];
PHP;
   }

   /**
    * Enregistre le ServiceProvider dans config/app.php
    */
   private function registerServiceProvider(SymfonyStyle $io): void
   {
      $io->section('Enregistrement du ServiceProvider');

      $appConfig = config_path('app.php');
      if (!file_exists($appConfig)) {
         $io->warning('Le fichier de configuration app.php n\'existe pas.');
         return;
      }

      $content = file_get_contents($appConfig);
      if (strpos($content, 'IronFlow\\Channel\\ChannelServiceProvider') !== false) {
         $io->note('Le ServiceProvider est déjà enregistré.');
         return;
      }

      // Ajouter le provider
      $content = preg_replace(
         '/(\'providers\' => \[)(.*?)(\])/s',
         "$1$2    IronFlow\\Channel\\ChannelServiceProvider::class,\n$3",
         $content
      );

      // Sauvegarder le fichier
      Filesystem::put($appConfig, $content);
      $io->success('ServiceProvider enregistré avec succès !');
   }

   /**
    * Publie les dépendances JavaScript
    */
   private function publishJsDependencies(SymfonyStyle $io): void
   {
      $io->section('Publication des dépendances JavaScript');

      $jsDir = public_path('js');
      if (!is_dir($jsDir)) {
         mkdir($jsDir, 0755, true);
      }

      $jsFile = $jsDir . '/channel.js';
      if (file_exists($jsFile)) {
         $overwrite = $io->confirm('Le fichier channel.js existe déjà. Voulez-vous le remplacer?', false);
         if (!$overwrite) {
            $io->note('Publication des dépendances JavaScript annulée.');
            return;
         }
      }

      $jsContent = $this->getJsTemplate();
      Filesystem::put($jsFile, $jsContent);

      $io->success('Dépendances JavaScript publiées avec succès !');
   }

   /**
    * Retourne le template JavaScript
    */
   private function getJsTemplate(): string
   {
      return <<<'JS'
/**
 * IronFlow Channel - Client JavaScript pour le système de channel
 */
class IronFlowChannel {
    constructor(options = {}) {
        this.options = Object.assign({
            baseUrl: '',
            authEndpoint: '/broadcasting/auth',
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            provider: document.querySelector('meta[name="channel-provider"]')?.getAttribute('content') || 'websocket'
        }, options);

        this.subscriptions = {};
        this.connected = false;
        
        this.init();
    }

    /**
     * Initialise la connexion
     */
    init() {
        if (this.options.provider === 'websocket') {
            this.initWebSocket();
        } else if (this.options.provider === 'pusher') {
            this.initPusher();
        } else if (this.options.provider === 'socketio') {
            this.initSocketIO();
        } else {
            console.error(`Provider non supporté: ${this.options.provider}`);
        }
    }

    /**
     * Initialise WebSocket
     */
    initWebSocket() {
        const host = document.querySelector('meta[name="channel-host"]')?.getAttribute('content') || '127.0.0.1';
        const port = document.querySelector('meta[name="channel-port"]')?.getAttribute('content') || '8080';
        const secure = document.querySelector('meta[name="channel-secure"]')?.getAttribute('content') === 'true';
        const path = document.querySelector('meta[name="channel-path"]')?.getAttribute('content') || '/socket';

        const protocol = secure ? 'wss' : 'ws';
        const url = `${protocol}://${host}:${port}${path}`;

        this.socket = new WebSocket(url);
        
        this.socket.onopen = () => {
            this.connected = true;
            console.log('WebSocket connecté');
        };
        
        this.socket.onclose = () => {
            this.connected = false;
            console.log('WebSocket déconnecté');
            
            // Tentative de reconnexion après 5 secondes
            setTimeout(() => this.initWebSocket(), 5000);
        };
        
        this.socket.onerror = (error) => {
            console.error('Erreur WebSocket:', error);
        };
        
        this.socket.onmessage = (event) => {
            try {
                const message = JSON.parse(event.data);
                if (message.channel && message.event) {
                    this.handleEvent(message.channel, message.event, message.data);
                }
            } catch (error) {
                console.error('Erreur de parsing JSON:', error);
            }
        };
    }

    /**
     * Initialise Pusher
     */
    initPusher() {
        // Vérifier si Pusher est disponible
        if (typeof Pusher === 'undefined') {
            console.error('Pusher n\'est pas disponible. Veuillez inclure la bibliothèque Pusher.');
            return;
        }

        const appKey = document.querySelector('meta[name="channel-key"]')?.getAttribute('content');
        const cluster = document.querySelector('meta[name="channel-cluster"]')?.getAttribute('content') || 'eu';
        
        if (!appKey) {
            console.error('La clé d\'application Pusher n\'est pas définie.');
            return;
        }

        this.pusher = new Pusher(appKey, {
            cluster: cluster,
            encrypted: true,
            authEndpoint: this.options.baseUrl + this.options.authEndpoint,
            auth: {
                headers: {
                    'X-CSRF-Token': this.options.csrfToken
                }
            }
        });
        
        this.connected = true;
    }

    /**
     * Initialise Socket.IO
     */
    initSocketIO() {
        // Vérifier si Socket.IO est disponible
        if (typeof io === 'undefined') {
            console.error('Socket.IO n\'est pas disponible. Veuillez inclure la bibliothèque Socket.IO.');
            return;
        }

        const host = document.querySelector('meta[name="channel-host"]')?.getAttribute('content') || '127.0.0.1';
        const port = document.querySelector('meta[name="channel-port"]')?.getAttribute('content') || '6001';
        const path = document.querySelector('meta[name="channel-path"]')?.getAttribute('content') || '/socket.io';
        
        this.io = io(`http://${host}:${port}`, {
            path: path,
            autoConnect: true,
            auth: {
                token: this.options.csrfToken
            }
        });
        
        this.io.on('connect', () => {
            this.connected = true;
            console.log('Socket.IO connecté');
        });
        
        this.io.on('disconnect', () => {
            this.connected = false;
            console.log('Socket.IO déconnecté');
        });
        
        this.io.on('error', (error) => {
            console.error('Erreur Socket.IO:', error);
        });
        
        this.io.on('message', (message) => {
            if (message.channel && message.event) {
                this.handleEvent(message.channel, message.event, message.data);
            }
        });
    }

    /**
     * Gère un événement reçu
     */
    handleEvent(channel, event, data) {
        if (this.subscriptions[channel]) {
            this.subscriptions[channel].forEach(callback => {
                callback({
                    channel: channel,
                    event: event,
                    data: data
                });
            });
        }
    }

    /**
     * S'abonne à un channel
     */
    subscribe(channel, callback) {
        if (!this.subscriptions[channel]) {
            this.subscriptions[channel] = [];
            
            // S'abonner au canal selon le provider
            if (this.options.provider === 'pusher' && this.pusher) {
                this.pusher.subscribe(channel);
            } else if (this.options.provider === 'socketio' && this.io) {
                this.io.emit('subscribe', { channel: channel });
            } else if (this.options.provider === 'websocket' && this.socket && this.connected) {
                this.socket.send(JSON.stringify({
                    action: 'subscribe',
                    channel: channel
                }));
            }
        }
        
        this.subscriptions[channel].push(callback);
        
        return {
            unsubscribe: () => this.unsubscribe(channel, callback)
        };
    }

    /**
     * Se désabonne d'un channel
     */
    unsubscribe(channel, callback = null) {
        if (!this.subscriptions[channel]) {
            return;
        }
        
        if (callback) {
            // Supprimer uniquement le callback spécifié
            this.subscriptions[channel] = this.subscriptions[channel].filter(cb => cb !== callback);
        } else {
            // Supprimer tous les callbacks
            delete this.subscriptions[channel];
        }
        
        // Si plus aucun callback pour ce canal, se désabonner complètement
        if (!this.subscriptions[channel] || this.subscriptions[channel].length === 0) {
            if (this.options.provider === 'pusher' && this.pusher) {
                this.pusher.unsubscribe(channel);
            } else if (this.options.provider === 'socketio' && this.io) {
                this.io.emit('unsubscribe', { channel: channel });
            } else if (this.options.provider === 'websocket' && this.socket && this.connected) {
                this.socket.send(JSON.stringify({
                    action: 'unsubscribe',
                    channel: channel
                }));
            }
            
            delete this.subscriptions[channel];
        }
    }

    /**
     * Diffuse un événement sur un channel
     */
    broadcast(channel, event, data = {}) {
        return fetch(this.options.baseUrl + '/broadcasting/broadcast', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.options.csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                channel: channel,
                event: event,
                data: data
            })
        })
        .then(response => response.json());
    }
}

// Exposer globalement
window.IronFlowChannel = IronFlowChannel;
JS;
   }

   /**
    * Met à jour le fichier .env
    */
   private function updateEnvFile(SymfonyStyle $io): void
   {
      $io->section('Mise à jour du fichier .env');

      $envFile = base_path('.env');
      if (!file_exists($envFile)) {
         $io->warning('Le fichier .env n\'existe pas.');
         return;
      }

      $content = file_get_contents($envFile);

      // Vérifier si les variables d'environnement sont déjà définies
      if (preg_match('/CHANNEL_PROVIDER/', $content)) {
         $io->note('Les variables d\'environnement liées au système de channel sont déjà définies.');
         return;
      }

      // Ajouter les variables d'environnement
      $envVars = <<<'EOT'

# Configuration du système de channel
CHANNEL_PROVIDER=websocket
CHANNEL_AUTH_ENABLED=true
CHANNEL_CACHE_TTL=3600

# Configuration WebSocket
WEBSOCKET_HOST=127.0.0.1
WEBSOCKET_PORT=8080
WEBSOCKET_PATH=/socket
WEBSOCKET_SECURE=false

# Configuration Pusher (si utilisé)
# PUSHER_APP_ID=
# PUSHER_APP_KEY=
# PUSHER_APP_SECRET=
# PUSHER_APP_CLUSTER=eu

# Configuration Socket.IO (si utilisé)
# SOCKETIO_HOST=127.0.0.1
# SOCKETIO_PORT=6001
# SOCKETIO_PATH=/socket.io
EOT;

      Filesystem::put($envFile, $envVars);
      $io->success('Fichier .env mis à jour avec succès !');
   }
}
