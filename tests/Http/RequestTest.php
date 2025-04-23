<?php

namespace IronFlow\Tests\Http;

use IronFlow\Http\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
   private Request $request;

   protected function setUp(): void
   {
      $_SERVER = [
         'REQUEST_METHOD' => 'GET',
         'REQUEST_URI' => '/test',
         'HTTP_HOST' => 'example.com',
         'HTTP_USER_AGENT' => 'PHPUnit',
         'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
         'HTTP_ACCEPT_LANGUAGE' => 'en-US,en;q=0.5',
         'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
         'HTTP_CONNECTION' => 'keep-alive',
         'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
         'HTTP_CACHE_CONTROL' => 'max-age=0',
      ];

      $_GET = ['param' => 'value'];
      $_POST = ['post_param' => 'post_value'];
      $_COOKIE = ['cookie' => 'value'];
      $_FILES = [
         'file' => [
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => '/tmp/test.txt',
            'error' => 0,
            'size' => 10,
         ],
      ];

      $this->request = new Request();
   }

   /**
    * Test que la requête peut récupérer la méthode HTTP
    */
   public function testRequestCanGetMethod(): void
   {
      $this->assertEquals('GET', $this->request->getMethod());
   }

   /**
    * Test que la requête peut récupérer l'URI
    */
   public function testRequestCanGetUri(): void
   {
      $this->assertEquals('/test', $this->request->getUri());
   }

   /**
    * Test que la requête peut récupérer l'hôte
    */
   public function testRequestCanGetHost(): void
   {
      $this->assertEquals('example.com', $this->request->getHost());
   }

   /**
    * Test que la requête peut récupérer l'agent utilisateur
    */
   public function testRequestCanGetUserAgent(): void
   {
      $this->assertEquals('PHPUnit', $this->request->userAgent());
   }

   /**
    * Test que la requête peut récupérer les paramètres GET
    */
   public function testRequestCanGetQueryParameters(): void
   {
      $this->assertEquals(['param' => 'value'], $this->request->all());
      $this->assertEquals('value', $this->request->all('param'));
   }

   /**
    * Test que la requête peut récupérer les paramètres POST
    */
   public function testRequestCanGetPostParameters(): void
   {
      $this->assertEquals(['post_param' => 'post_value'], $this->request->all());
      $this->assertEquals('post_value', $this->request->all('post_param'));
   }

   /**
    * Test que la requête peut récupérer les cookies
    */
   public function testRequestCanGetCookies(): void
   {
      $this->assertEquals(['cookie' => 'value'], $this->request->cookies());
      $this->assertEquals('value', $this->request->cookies('cookie'));
   }

   /**
    * Test que la requête peut récupérer les fichiers
    */
   public function testRequestCanGetFiles(): void
   {
      $this->assertEquals($_FILES, $this->request->files());
      $this->assertEquals($_FILES['file'], $this->request->files('file'));
   }

   /**
    * Test que la requête peut récupérer les en-têtes
    */
   public function testRequestCanGetHeaders(): void
   {
      $headers = $this->request->getHeaders();

      $this->assertIsArray($headers);
      $this->assertArrayHasKey('Host', $headers);
      $this->assertEquals('example.com', $headers['Host']);
   }

   /**
    * Test que la requête peut vérifier si elle est en AJAX
    */
   public function testRequestCanCheckIfAjax(): void
   {
      $this->assertFalse($this->request->isAjax());

      $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
      $request = new Request();

      $this->assertTrue($request->isAjax());
   }

   /**
    * Test que la requête peut vérifier si elle est en JSON
    */
   public function testRequestCanCheckIfJson(): void
   {
      $this->assertFalse($this->request->isJson());

      $_SERVER['CONTENT_TYPE'] = 'application/json';
      $request = new Request();

      $this->assertTrue($request->isJson());
   }
}
