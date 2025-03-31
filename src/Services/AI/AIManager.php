<?php

namespace IronFlow\Services\AI;

use IronFlow\Core\Application;
use IronFlow\Support\Facades\Config;
use InvalidArgumentException;

class AIManager
{
   protected Application $app;
   protected array $providers = [];
   protected ?AIProvider $defaultProvider = null;

   public function __construct(Application $app)
   {
      $this->app = $app;
   }

   /**
    * Obtient un fournisseur d'IA par son nom
    * 
    * @param string|null $provider Nom du fournisseur (si null, utilise le fournisseur par défaut)
    * @return AIProvider
    */
   public function provider(?string $provider = null): AIProvider
   {
      $provider = $provider ?? Config::get('ai.default', 'openai');

      if (isset($this->providers[$provider])) {
         return $this->providers[$provider];
      }

      $method = 'create' . ucfirst($provider) . 'Provider';

      if (method_exists($this, $method)) {
         return $this->providers[$provider] = $this->$method();
      }

      $providerClass = $this->resolveProviderClass($provider);

      if (class_exists($providerClass)) {
         $config = Config::get('ai.providers.' . $provider, []);
         return $this->providers[$provider] = new $providerClass($config);
      }

      throw new InvalidArgumentException("Le fournisseur d'IA [{$provider}] n'est pas pris en charge.");
   }

   /**
    * Détermine la classe de fournisseur pour un driver donné
    */
   protected function resolveProviderClass(string $provider): string
   {
      $knownDrivers = [
         'openai' => 'IronFlow\Framework\AI\Providers\OpenAIProvider',
         'anthropic' => 'IronFlow\Framework\AI\Providers\AnthropicProvider',
         'gemini' => 'IronFlow\Framework\AI\Providers\GoogleAIProvider',
      ];

      return $knownDrivers[$provider] ?? 'IronFlow\Framework\AI\Providers\\' . ucfirst($provider) . 'Provider';
   }

   /**
    * Crée une instance du fournisseur OpenAI
    */
   protected function createOpenaiProvider(): AIProvider
   {
      return $this->app->make('ai.openai');
   }

   /**
    * Crée une instance du fournisseur Anthropic (Claude)
    */
   protected function createAnthropicProvider(): AIProvider
   {
      return $this->app->make('ai.anthropic');
   }

   /**
    * Crée une instance du fournisseur Google AI (Gemini)
    */
   protected function createGeminiProvider(): AIProvider
   {
      return $this->app->make('ai.gemini');
   }

   /**
    * Génère du texte avec le fournisseur spécifié
    *
    * @param string $prompt Message pour l'IA
    * @param array $options Options supplémentaires
    * @param string|null $provider Nom du fournisseur (si null, utilise le fournisseur par défaut)
    * @return string Réponse générée
    */
   public function generate(string $prompt, array $options = [], ?string $provider = null): string
   {
      return $this->provider($provider)->generate($prompt, $options);
   }

   /**
    * Génère des compléments pour un prompt donné avec le fournisseur spécifié
    *
    * @param string $prompt Message pour l'IA
    * @param array $options Options supplémentaires
    * @param string|null $provider Nom du fournisseur (si null, utilise le fournisseur par défaut)
    * @return array Réponse générée avec des métadonnées
    */
   public function completion(string $prompt, array $options = [], ?string $provider = null): array
   {
      return $this->provider($provider)->completion($prompt, $options);
   }

   /**
    * Génère une réponse de chat avec le fournisseur spécifié
    *
    * @param array $messages Messages pour l'IA au format [{role, content}]
    * @param array $options Options supplémentaires
    * @param string|null $provider Nom du fournisseur (si null, utilise le fournisseur par défaut)
    * @return array Réponse générée avec des métadonnées
    */
   public function chat(array $messages, array $options = [], ?string $provider = null): array
   {
      return $this->provider($provider)->chat($messages, $options);
   }

   /**
    * Transmets dynamiquement les méthodes au fournisseur par défaut
    */
   public function __call(string $method, array $parameters)
   {
      return $this->provider()->$method(...$parameters);
   }
}
