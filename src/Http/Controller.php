<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\View\ViewInterface;
use IronFlow\Http\Response;
use IronFlow\Routing\Router;
use IronFlow\View\TwigView;
use IronFlow\Validation\Validator;

abstract class Controller
{
   protected TwigView $view;
   protected Response $response;

   public function __construct(?ViewInterface $view = null)
   {
      if ($view === null) {
         $viewPath = view_path();
         if (!is_dir($viewPath)) {
            throw new \RuntimeException("Le répertoire des vues n'existe pas: {$viewPath}");
         }
         $this->view = new TwigView($viewPath);
      } else {
         $this->view = $view;
      }
      $this->response = new Response();
   }

   protected function view(string $template, array $data = []): Response
   {
      $content = $this->view->render($template, $data);
      return $this->response->setContent($content);
   }

   protected function json(array $data, int $status = 200): Response
   {
      return $this->response
         ->setContent(json_encode($data))
         ->setStatusCode($status)
         ->setHeader('Content-Type', 'application/json');
   }

   protected function redirect(?string $url = null): Response
   {
      if ($url) {
         return $this->response
            ->setStatusCode(302)
            ->setHeader('Location', $url);
      }

      // Retourne une réponse vide avec code 302 si aucune URL n'est fournie
      return $this->response->setStatusCode(302);
   }

   public function route(string $name, array $parameters = []): Response
   {
      $router = new Router();
      $url = $router->url($name, $parameters);
      return $this->redirect($url);
   }

   public function with(string $key, mixed $value): self
   {
      if (!session_status() === PHP_SESSION_ACTIVE) {
         session_start();
      }

      $_SESSION['_flash'][$key] = $value;
      $this->response->with($key, $value);

      return $this;
   }

   protected function withErrors(array $errors): self
   {
      $this->with('errors', $errors);
      return $this;
   }

   protected function withOld(array $old): self
   {
      $this->with('old', $old);
      return $this;
   }

   protected function withInput(): self
   {
      $this->withOld($_POST);
      return $this;
   }

   protected function abort(int $status, string $message = "Page non trouvée"): void
   {
      $this->response->setStatusCode($status)->setContent($message);
      $this->response->send();
      exit;
   }

   protected function response(): Response
   {
      return $this->response;
   }

   protected function back(): Response
   {
      $referer = $_SERVER['HTTP_REFERER'] ?? '/';
      return $this->redirect($referer);
   }

   /**
    * Valide les données selon les règles spécifiées
    * 
    * @param array $data Les données à valider
    * @param array $rules Les règles de validation
    * @return array|bool Retourne true si la validation réussit, sinon un tableau d'erreurs
    */
   protected function validate(array $data, array $rules): array|bool
   {
      $validator = new Validator($data, $rules);

      if ($validator->validate()) {
         return true;
      }

      return $validator->errors();
   }

   /**
    * Valide les données et redirige avec les erreurs si la validation échoue
    * 
    * @param array $data Les données à valider
    * @param array $rules Les règles de validation
    * @param string|null $redirectTo URL de redirection en cas d'échec
    * @return bool Retourne true si la validation réussit
    */
   protected function validateOrFail(array $data, array $rules, ?string $redirectTo = null): bool
   {
      $result = $this->validate($data, $rules);

      if ($result !== true) {
         $this->with('errors', $result);
         $this->with('old', $data);

         if ($redirectTo) {
            $this->redirect($redirectTo)->send();
         } else {
            $this->back()->send();
         }

         exit;
      }

      return true;
   }
}
