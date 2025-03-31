<?php

namespace IronFlow\Services\AI\Providers;

use IronFlow\Services\AI\AIProvider;
use IronFlow\Support\Facades\Config;
use InvalidArgumentException;
use Exception;
use GuzzleHttp\Client;

class OpenAIProvider implements AIProvider
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
            throw new InvalidArgumentException("Clé API OpenAI non définie.");
         }

         $this->client = new Client([
            'base_uri' => $this->config['base_uri'] ?? 'https://api.openai.com/v1',
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
               'Authorization' => 'Bearer ' . $this->config['api_key'],
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
      $model = $options['model'] ?? $this->config['model'] ?? 'gpt-4-turbo';
      $temperature = $options['temperature'] ?? $this->defaultOptions['temperature'];
      $maxTokens = $options['max_tokens'] ?? $this->defaultOptions['max_tokens'];

      try {
         $response = $this->getClient()->post('completions', [
            'json' => [
               'model' => $model,
               'prompt' => $prompt,
               'temperature' => $temperature,
               'max_tokens' => $maxTokens,
               'n' => 1,
               'stop' => $options['stop'] ?? null,
            ]
         ]);

         $result = json_decode((string) $response->getBody(), true);

         return [
            'content' => $result['choices'][0]['text'] ?? '',
            'model' => $model,
            'usage' => $result['usage'] ?? null,
            'id' => $result['id'] ?? null,
            'raw' => $result,
         ];
      } catch (Exception $e) {
         return [
            'content' => '',
            'error' => $e->getMessage(),
            'raw' => null,
         ];
      }
   }

   /**
    * {@inheritdoc}
    */
   public function chat(array $messages, array $options = []): array
   {
      $model = $options['model'] ?? $this->config['model'] ?? 'gpt-4-turbo';
      $temperature = $options['temperature'] ?? $this->defaultOptions['temperature'];
      $maxTokens = $options['max_tokens'] ?? $this->defaultOptions['max_tokens'];

      try {
         $response = $this->getClient()->post('chat/completions', [
            'json' => [
               'model' => $model,
               'messages' => $messages,
               'temperature' => $temperature,
               'max_tokens' => $maxTokens,
               'n' => 1,
               'stop' => $options['stop'] ?? null,
               'stream' => $options['streaming'] ?? $this->defaultOptions['streaming'],
            ]
         ]);

         $result = json_decode((string) $response->getBody(), true);

         return [
            'content' => $result['choices'][0]['message']['content'] ?? '',
            'role' => $result['choices'][0]['message']['role'] ?? 'assistant',
            'model' => $model,
            'usage' => $result['usage'] ?? null,
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
