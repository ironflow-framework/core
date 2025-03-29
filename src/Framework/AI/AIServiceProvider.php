<?php

namespace IronFlow\Framework\AI;

use IronFlow\Application\ServiceProvider;
use IronFlow\Framework\AI\Providers\OpenAIProvider;
use IronFlow\Framework\AI\Providers\AnthropicProvider;
use IronFlow\Framework\AI\Providers\GoogleAIProvider;
use IronFlow\Support\Facades\Config;

class AIServiceProvider extends ServiceProvider
{
   public function register(): void
   {
      $this->app->singleton('ai', function ($app) {
         return new AIManager($app);
      });

      $this->app->singleton('ai.openai', function ($app) {
         $config = Config::get('ai.providers.openai', []);
         return new OpenAIProvider($config);
      });

      $this->app->singleton('ai.anthropic', function ($app) {
         $config = Config::get('ai.providers.anthropic', []);
         return new AnthropicProvider($config);
      });

      $this->app->singleton('ai.gemini', function ($app) {
         $config = Config::get('ai.providers.gemini', []);
         return new GoogleAIProvider($config);
      });
   }

   public function boot(): void
   {
      $this->publishes([
         __DIR__ . '/../../config/ai.php' => config_path('ai.php'),
      ], 'config');

      if (!file_exists(config_path('ai.php'))) {
         $this->createDefaultConfig();
      }
   }

   private function createDefaultConfig(): void
   {
      $configContent = <<<'PHP'
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Services d'IA
    |--------------------------------------------------------------------------
    |
    | Ce fichier contient la configuration pour les différents services d'IA
    | intégrés dans l'application.
    |
    */

    'default' => env('AI_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'api_key' => env('OPENAI_API_KEY'),
            'organization' => env('OPENAI_ORGANIZATION'),
            'model' => env('OPENAI_MODEL', 'gpt-4-turbo'),
            'base_uri' => env('OPENAI_BASE_URI', 'https://api.openai.com/v1'),
            'timeout' => env('OPENAI_TIMEOUT', 30),
        ],
        
        'anthropic' => [
            'api_key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-3-opus-20240229'),
            'base_uri' => env('ANTHROPIC_BASE_URI', 'https://api.anthropic.com'),
            'timeout' => env('ANTHROPIC_TIMEOUT', 30),
        ],
        
        'gemini' => [
            'api_key' => env('GOOGLE_AI_API_KEY'),
            'model' => env('GOOGLE_AI_MODEL', 'gemini-1.5-pro'),
            'base_uri' => env('GOOGLE_AI_BASE_URI', 'https://generativelanguage.googleapis.com'),
            'timeout' => env('GOOGLE_AI_TIMEOUT', 30),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres par défaut
    |--------------------------------------------------------------------------
    |
    | Ces paramètres seront utilisés comme valeurs par défaut pour les appels aux API.
    |
    */
    'defaults' => [
        'temperature' => 0.7,
        'max_tokens' => 1000,
        'streaming' => false,
    ],
];
PHP;

      if (!file_exists(dirname(config_path('ai.php')))) {
         mkdir(dirname(config_path('ai.php')), 0755, true);
      }

      file_put_contents(config_path('ai.php'), $configContent);
   }
}
