<?php

namespace IronFlow\Framework\AI\Providers;

use IronFlow\Framework\AI\AIProvider;
use IronFlow\Support\Facades\Config;
use InvalidArgumentException;
use Exception;
use GuzzleHttp\Client;

class AnthropicProvider implements AIProvider
{
   protected array $config;
   protected ?Client $client = null;
   protected array $defaultOptions;

   public function __construct(array $config = [])
   {
      $this->config = $config;
      $this->defaultOptions = Config::get('ai.defaults', [
         'temperature' => 0.7,
         'max_tokens' => 1000,
         'streaming' => false,
      ]);
   }

   /**
    * Obtient le client HTTP pour les appels API
    */
   protected function getClient(): Client
   {
      if ($this->client === null) {
         if (empty($this->config['api_key'])) {
            throw new InvalidArgumentException("Clé API Anthropic non définie.");
         }

         $this->client = new Client([
            'base_uri' => $this->config['base_uri'] ?? 'https://api.anthropic.com',
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
               'x-api-key' => $this->config['api_key'],
               'anthropic-version' => '2023-06-01',
               'Content-Type' => 'application/json',
               'Accept' => 'application/json',
            ],
         ]);
      }

      return $this->client;
   }

   /**
    * {@inheritdoc}
    */
   public function generate(string $prompt, array $options = []): string
   {
      $result = $this->completion($prompt, $options);
      return $result['content'] ?? '';
   }

   /**
    * {@inheritdoc}
    */
   public function completion(string $prompt, array $options = []): array
   {
      // Claude n'a pas d'API de completion standard, utilisons l'API de chat
      return $this->chat([
         ['role' => 'user', 'content' => $prompt]
      ], $options);
   }

   /**
    * {@inheritdoc}
    */
   public function chat(array $messages, array $options = []): array
   {
      $model = $options['model'] ?? $this->config['model'] ?? 'claude-3-opus-20240229';
      $temperature = $options['temperature'] ?? $this->defaultOptions['temperature'];
      $maxTokens = $options['max_tokens'] ?? $this->defaultOptions['max_tokens'];

      // Formatage spécifique à Anthropic
      $formattedMessages = [];
      foreach ($messages as $message) {
         $role = $message['role'];
         // Anthropic utilise 'human' au lieu de 'user'
         if ($role === 'user') {
            $role = 'human';
         }
         // Anthropic utilise 'assistant' comme Claude
         $formattedMessages[] = [
            'role' => $role,
            'content' => $message['content']
         ];
      }

      try {
         $response = $this->getClient()->post('v1/messages', [
            'json' => [
               'model' => $model,
               'messages' => $formattedMessages,
               'temperature' => $temperature,
               'max_tokens' => $maxTokens,
               'stream' => $options['streaming'] ?? $this->defaultOptions['streaming'],
               'system' => $options['system'] ?? null,
            ]
         ]);

         $result = json_decode((string) $response->getBody(), true);

         return [
            'content' => $result['content'][0]['text'] ?? '',
            'role' => 'assistant',
            'model' => $model,
            'usage' => [
               'input_tokens' => $result['usage']['input_tokens'] ?? 0,
               'output_tokens' => $result['usage']['output_tokens'] ?? 0,
            ],
            'id' => $result['id'] ?? null,
            'raw' => $result,
         ];
      } catch (Exception $e) {
         return [
            'content' => '',
            'role' => 'assistant',
            'error' => $e->getMessage(),
            'raw' => null,
         ];
      }
   }
}
