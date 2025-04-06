<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\Core\Application\Application;
use IronFlow\View\ViewInterface;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Auth;
use IronFlow\View\TwigView;
use IronFlow\Validation\Validator;
use IronFlow\Support\Facades\View;
use IronFlow\Support\Facades\Redirect;

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

   /**
    * Affiche une vue
    */
   protected function view(string $name, array $data = []): Response
   {
      $content = View::render($name, $data);
      return new Response($content);
   }

   /**
    * Redirige vers une URL
    */
   protected function redirect(string $url): Response
   {
      return Redirect::to($url);
   }

   /**
    * Redirige vers une route nommée
    */
   protected function redirectToRoute(string $name, array $parameters = []): Response
   {
      return Redirect::route($name, $parameters);
   }

   /**
    * Redirige vers l'URL précédente
    */
   protected function redirectBack(): Response
   {
      return Redirect::back();
   }

   /**
    * Redirige vers l'URL prévue ou une URL par défaut
    */
   protected function redirectIntended(string $default = '/'): Response
   {
      return Redirect::intended($default);
   }

   protected function json(array $data, int $status = 200): Response
   {
      return Response::json($data, $status);
   }

   public function route(string $name, array $parameters = []): Response
   {
      $router = Application::getInstance()->getRouter();
      $url = $router->generateUrl($name, $parameters);
      return $this->redirect($url);
   }

   protected function abort(int $status, string $message = "Page non trouvée"): never
   {
      throw new \IronFlow\Http\Exceptions\HttpException($message, $status);
   }

   protected function authorize(string $ability, $model): void
    {
        if (!Auth::can($ability, $model)) {
            abort(403);
        }
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

   protected function middleware(string|array $middleware): self
   {
      $this->middleware = array_merge(
         $this->middleware,
         (array) $middleware
      );
      return $this;
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
         $this->response()->withErrors([$result])->withInput();

         if ($redirectTo) {
            return $this->redirect($redirectTo);
         }

         return $this->back();
      }

      return true;
   }

}
