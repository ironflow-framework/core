<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\Core\Application\Application;
use IronFlow\View\ViewInterface;
use IronFlow\Http\Response\Response;
use IronFlow\Routing\Router;
use IronFlow\View\TwigView;
use IronFlow\Validation\Validator;
use IronFlow\Support\Facades\Session;

abstract class Controller
{
   protected TwigView $view;
   protected Response $response;
   protected array $middleware = [];

   public function __construct(?ViewInterface $view = null)
   {
      if ($view === null) {
         $this->view = Application::getInstance()->getContainer()->get('view');
      } else {
         $this->view = $view;
      }

      $this->response = new Response();
      $this->initialize();
   }

   /**
    * Méthode appelée après la construction du contrôleur
    */
   protected function initialize(): void
   {
      // À surcharger dans les classes enfants si nécessaire
   }

   /**
    * Récupère les middlewares du contrôleur
    */
   public function getMiddleware(): array
   {
      return $this->middleware;
   }

   protected function view(string $template, array $data = []): Response
   {
      $content = $this->view->render($template, $data);
      return $this->response->setContent($content);
   }

   protected function json(array $data, int $status = 200): Response
   {
      return Response::json($data, $status);
   }

   protected function redirect(?string $url = null): Response
   {
      return Response::redirect($url ?? '/');
   }

   public function route(string $name, array $parameters = []): Response
   {
      $router = Application::getInstance()->getRouter();
      $url = $router->generateUrl($name, $parameters);
      return $this->redirect($url);
   }

   public function with(string $key, mixed $value): self
   {
      Session::flash($key, $value);
      return $this;
   }

   protected function withErrors(array $errors): self
   {
      return $this->with('errors', $errors);
   }

   protected function withOld(array $old): self
   {
      return $this->with('old', $old);
   }

   protected function withInput(): self
   {
      return $this->withOld($_POST);
   }

   protected function abort(int $status, string $message = "Page non trouvée"): never
   {
      throw new \IronFlow\Http\Exceptions\HttpException($message, $status);
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
      $validator = Validator::make($data, $rules);
      return $validator->passes() ? true : $validator->getErrors();
   }

   /**
    * Valide les données et redirige avec les erreurs si la validation échoue
    * 
    * @param array $data Les données à valider
    * @param array $rules Les règles de validation
    * @param string|null $redirectTo URL de redirection en cas d'échec
    * @return Response|bool Retourne true si la validation réussit
    */
   protected function validateOrFail(array $data, array $rules, ?string $redirectTo = null): Response|bool
   {
      $result = $this->validate($data, $rules);

      if ($result !== true) {
         $this->withErrors($result)->withInput();

         if ($redirectTo) {
            return $this->redirect($redirectTo);
         }

         return $this->back();
      }

      return true;
   }

   protected function authorize(bool $condition, string $message = "Non autorisé"): void
   {
      if (!$condition) {
         $this->abort(403, $message);
      }
   }

   protected function middleware(string|array $middleware): self
   {
      $this->middleware = array_merge(
         $this->middleware,
         (array) $middleware
      );
      return $this;
   }

   public function index(): Response
   {
      try {
         return $this->view('welcome', [
            'APP_VERSION' => Application::VERSION
         ]);
      } catch (\Exception $e) {
         error_log($e->getMessage());
         error_log($e->getTraceAsString());
         throw $e;
      }
   }
}
