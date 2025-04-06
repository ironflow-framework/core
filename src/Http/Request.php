<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\Http\Exceptions\ValidationException;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use IronFlow\Support\Collection;
use IronFlow\Validation\Validator;

/**
 * Classe de requête HTTP
 * 
 * Cette classe encapsule une requête HTTP
 */
class Request extends HttpFoundationRequest
{
   /**
    * Crée une nouvelle instance de la requête
    * 
    * @param array $query Les paramètres de requête
    * @param array $request Les paramètres de requête POST
    * @param array $attributes Les attributs de requête
    * @param array $cookies Les cookies
    * @param array $files Les fichiers uploadés
    * @param array $server Les variables serveur
    * @param string|null $content Le contenu brut de la requête
    */
   public function __construct(
      array $query = [],
      array $request = [],
      array $attributes = [],
      array $cookies = [],
      array $files = [],
      array $server = [],
      ?string $content = null
   ) {
      parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
   }

   /**
    * Crée une requête à partir des variables globales
    * 
    * @return static
    */
   public static function createFromGlobals(): static
   {
      return new static(
         $_GET,
         $_POST,
         [],
         $_COOKIE,
         $_FILES,
         $_SERVER
      );
   }

   /**
    * Récupère une valeur de la requête
    * 
    * @param string $key La clé
    * @param mixed $default La valeur par défaut
    * @return mixed
    */
   public function input(string $key, mixed $default = null): mixed
   {
      return $this->request->get($key, $this->query->get($key, $default));
   }

   /**
    * Récupère une valeur de la requête ou des paramètres de requête
    * 
    * @param string $key La clé
    * @param mixed $default La valeur par défaut
    * @return mixed
    */
   public function get(string $key, mixed $default = null): mixed
   {
      return $this->query->get($key, $this->request->get($key, $default));
   }

   /**
    * Récupère tous les paramètres de la requête
    * 
    * @return array<string, mixed>
    */
   public function all(): array
   {
      return array_merge($this->query->all(), $this->request->all());
   }

   /**
    * Vérifie si la requête contient une clé
    * 
    * @param string $key La clé
    * @return bool
    */
   public function has(string $key): bool
   {
      return $this->query->has($key) || $this->request->has($key);
   }

   public function only(array $keys): array
   {
      $data = [];
      foreach ($keys as $key) {
         if ($this->query->keys() === $key) {
            $data[] = $this->query->get($key, $this->request->get($key, $this->query->get($key)));
         }
      }

      return $data;
   }

   public function except(array $keys): array
   {  
      $data = [];

      foreach ($keys as $key) {
         if ($this->query->keys() !== $key) {
            $data[] = $this->query->get($key, $this->request->get($key, $this->query->get($key)));
         }
      }

      return $data;
   }

   /**
    * Récupère un fichier uploadé
    * 
    * @param string $key La clé du fichier
    * @return \Symfony\Component\HttpFoundation\File\UploadedFile|null
    */
   public function file(string $key): ?\Symfony\Component\HttpFoundation\File\UploadedFile
   {
      return $this->files->get($key);
   }

   /**
    * Vérifie si la requête contient un fichier
    * 
    * @param string $key La clé du fichier
    * @return bool
    */
   public function hasFile(string $key): bool
   {
      return $this->files->has($key) && $this->files->get($key)->getError() !== UPLOAD_ERR_NO_FILE;
   }

   /**
    * Valide les données de la requête
    * 
    * @param array $rules Les règles de validation
    * @return array Les données validées
    */
   public function validate(array $rules): array
   {
      $validator = new Validator($this->all(), $rules);

      if (!$validator->passes()) {
         throw new ValidationException($validator->errors());
      }

      return $validator->validated();
   }

   /**
    * Vérifie si la requête est en AJAX
    * 
    * @return bool
    */
   public function isAjax(): bool
   {
      return $this->headers->get('X-Requested-With') === 'XMLHttpRequest';
   }

   /**
    * Vérifie si la requête est en JSON
    * 
    * @return bool
    */
   public function isJson(): bool
   {
      return str_contains($this->headers->get('Content-Type', ''), 'application/json');
   }

   /**
    * Récupère le contenu JSON de la requête
    * 
    * @param bool $assoc Si true, retourne un tableau associatif
    * @return mixed
    */
   public function json(bool $assoc = true): mixed
   {
      return json_decode($this->getContent(), $assoc);
   }

   /**
    * Récupère l'URL de la requête
    * 
    * @return string
    */
   public function url(): string
   {
      return $this->getUri();
   }

   /**
    * Récupère le chemin de la requête
    * 
    * @return string
    */
   public function path(): string
   {
      return $this->getPathInfo();
   }

   /**
    * Récupère la méthode HTTP de la requête
    * 
    * @return string
    */
   public function method(): string
   {
      return $this->getMethod();
   }

   /**
    * Vérifie si la méthode HTTP correspond
    * 
    * @param string $method POST | GET La méthode à vérifier
    * @return bool
    */
   public function isMethod(string $method): bool
   {
      return strtoupper($method) === $this->method();
   }

   /**
    * Récupère l'adresse IP du client
    * 
    * @return string
    */
   public function ip(): string
   {
      return $this->getClientIp() ?? '';
   }

   /**
    * Récupère l'agent utilisateur
    * 
    * @return string
    */
   public function userAgent(): string
   {
      return $this->headers->get('User-Agent', '');
   }

   /**
    * Récupère un paramètre sous forme de booléen
    * 
    * @param string $key La clé du paramètre
    * @param bool $default La valeur par défaut si le paramètre n'existe pas
    * @return bool
    */
   public function boolean(string $key, bool $default = false): bool
   {
      $value = $this->get($key, $default);
      
      if (is_bool($value)) {
         return $value;
      }

      if (is_string($value)) {
         return in_array(strtolower($value), ['true', '1', 'on', 'yes'], true);
      }

      if (is_numeric($value)) {
         return $value == 1;
      }

      return $default;
   }
}
