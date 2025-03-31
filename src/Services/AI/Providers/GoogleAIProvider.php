<?php

namespace IronFlow\Services\AI\Providers;

use IronFlow\Services\AI\AIProvider;
use IronFlow\Support\Facades\Config;
use InvalidArgumentException;
use Exception;
use GuzzleHttp\Client;

class GoogleAIProvider implements AIProvider
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
            throw new InvalidArgumentException("Clé API Google AI non définie.");
         }

         $this->client = new Client([
            'base_uri' => $this->config['base_uri'] ?? 'https://generativelanguage.googleapis.com',
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
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
      // Gemini n'a pas d'API de completion standard, utilisons l'API de chat
      return $this->chat([
         ['role' => 'user', 'content' => $prompt]
      ], $options);
   }

   /**
    * {@inheritdoc}
    */
   public function chat(array $messages, array $options = []): array
   {
      $model = $options['model'] ?? $this->config['model'] ?? 'gemini-1.5-pro';
      $temperature = $options['temperature'] ?? $this->defaultOptions['temperature'];
      $maxTokens = $options['max_tokens'] ?? $this->defaultOptions['max_tokens'];
      $apiKey = $this->config['api_key'];

      // Formatage spécifique à Google AI (Gemini)
      $formattedMessages = [];
      foreach ($messages as $message) {
         $formattedMessages[] = [
            'role' => $message['role'],
            'parts' => [
               ['text' => $message['content']]
            ]
         ];
      }

      try {
         $endpoint = "/v1beta/models/{$model}:generateContent?key={$apiKey}";

         $response = $this->getClient()->post($endpoint, [
            'json' => [
               'contents' => $formattedMessages,
               'generationConfig' => [
                  'temperature' => $temperature,
                  'maxOutputTokens' => $maxTokens,
                  'topP' => $options['top_p'] ?? 0.95,
                  'topK' => $options['top_k'] ?? 40,
               ]
            ]
         ]);

         $result = json_decode((string) $response->getBody(), true);

         if (!isset($result['candidates'][0]['content'])) {
            throw new Exception("Réponse invalide de l'API Google AI");
         }

         $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

         return [
            'content' => $content,
            'role' => 'assistant',
            'model' => $model,
            'usage' => [
               'input_tokens' => $result['usage']['promptTokenCount'] ?? 0,
               'output_tokens' => $result['usage']['candidatesTokenCount'] ?? 0,
            ],
            'raw' => $result,
         ];
      } catch (Exception $e) {
         throw new Exception("Erreur Google AI: " . $e->getMessage());
      }
   }
}
