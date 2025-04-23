<?php

namespace IronFlow\Tests\Http;

use IronFlow\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
   private Response $response;

   protected function setUp(): void
   {
      $this->response = new Response();
   }

   /**
    * Test que la réponse peut définir et récupérer le contenu
    */
   public function testResponseCanSetAndGetContent(): void
   {
      $content = 'Test content';

      $this->response->setContent($content);

      $this->assertEquals($content, $this->response->getContent());
   }

   /**
    * Test que la réponse peut définir et récupérer le code de statut
    */
   public function testResponseCanSetAndGetStatusCode(): void
   {
      $statusCode = 201;

      $this->response->setStatusCode($statusCode);

      $this->assertEquals($statusCode, $this->response->getStatusCode());
   }

   /**
    * Test que la réponse peut définir et récupérer les en-têtes
    */
   public function testResponseCanSetAndGetHeaders(): void
   {
      $headers = [
         'Content-Type' => 'application/json',
         'X-Custom-Header' => 'custom-value',
      ];

      foreach ($headers as $name => $value) {
         $this->response->setHeader($name, $value);
      }

      $this->assertEquals($headers, $this->response->getHeaders());
   }

   /**
    * Test que la réponse peut définir et récupérer un cookie
    */
   public function testResponseCanSetAndGetCookies(): void
   {
      $name = 'test-cookie';
      $value = 'cookie-value';
      $expire = time() + 3600;
      $path = '/';
      $domain = 'example.com';
      $secure = true;
      $httpOnly = true;

      $this->response->setCookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);

      $cookies = $this->response->getCookies();

      $this->assertCount(1, $cookies);
      $this->assertEquals($name, $cookies[0]['name']);
      $this->assertEquals($value, $cookies[0]['value']);
      $this->assertEquals($expire, $cookies[0]['expire']);
      $this->assertEquals($path, $cookies[0]['path']);
      $this->assertEquals($domain, $cookies[0]['domain']);
      $this->assertEquals($secure, $cookies[0]['secure']);
      $this->assertEquals($httpOnly, $cookies[0]['httpOnly']);
   }

   /**
    * Test que la réponse peut être envoyée
    */
   public function testResponseCanBeSent(): void
   {
      $content = 'Test content';
      $statusCode = 200;
      $headers = [
         'Content-Type' => 'text/plain',
      ];

      $this->response->setContent($content);
      $this->response->setStatusCode($statusCode);

      foreach ($headers as $name => $value) {
         $this->response->setHeader($name, $value);
      }

      // Capture la sortie
      ob_start();
      $this->response->send();
      $output = ob_get_clean();

      $this->assertEquals($content, $output);
   }

   /**
    * Test que la réponse peut être convertie en JSON
    */
   public function testResponseCanBeJson(): void
   {
      $data = [
         'name' => 'Test',
         'value' => 123,
      ];

      $this->response->json($data);

      $this->assertEquals('application/json', $this->response->getHeaders()['Content-Type']);
      $this->assertEquals(json_encode($data), $this->response->getContent());
   }

   /**
    * Test que la réponse peut être redirigée
    */
   public function testResponseCanBeRedirected(): void
   {
      $url = 'https://example.com';

      $this->response->redirect($url);

      $this->assertEquals(302, $this->response->getStatusCode());
      $this->assertEquals($url, $this->response->getHeaders()['Location']);
   }

   /**
    * Test que la réponse peut être une erreur
    */
   public function testResponseCanBeError(): void
   {
      $message = 'Not Found';
      $statusCode = 404;

      $this->response->error($message, $statusCode);

      $this->assertEquals($statusCode, $this->response->getStatusCode());
      $this->assertEquals($message, $this->response->getContent());
   }
}
