<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\Core\Application\Application;
use IronFlow\Database\Model;
use IronFlow\Http\Exceptions\HttpException;
use IronFlow\View\ViewInterface;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Auth;
use IronFlow\View\TwigView;
use IronFlow\Validation\Validator;
use IronFlow\Support\Facades\View;
use IronFlow\Support\Facades\Redirect;

/**
 * Abstract Class pour les controllers
 * 
 * Cette classe represente la classe de base pour les controllers.
 */
abstract class Controller
{
   protected TwigView $view;
   protected Response $response;
   protected array $middleware = [];

   /**
    * Contructeur de la classe Controller
    *
    * @param mixed $view
    */
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
    * Cette methode peut-être surcharger dans les classes enfants si necessaire
    *
    * @return void
    */
   protected function initialize(): void
   {
      // À surcharger dans les classes enfants si nécessaire
   }

   /**
    * Récupère les middlewares du contrôleur
    *
    * @return array
    */
   public function getMiddleware(): array
   {
      return $this->middleware;
   }

   /**
    * Rendre une vue
    *
    * @param string $name 
    * @param array $data 
    * @return \IronFlow\Http\Response
    */
   protected function view(string $name, array $data = []): Response
   {
      $content = View::render($name, $data);
      return new Response($content);
   }

   /**
    * Redirige vers une URL
    *
    * @param string $url 
    * @return \IronFlow\Http\Response
    */
   protected function redirect(string $url): Response
   {
      return Redirect::to($url);
   }

   /**
    * Redirige vers une route nommée
    *
    * @param string $name
    * @param array $parameters
    * @return \IronFlow\Http\Response  
    */
   protected function redirectToRoute(string $name, array $parameters = []): Response
   {
      return Redirect::route($name, $parameters);
   }

   /**
    * Redirige vers l'URL précédente
    *
    * @return \IronFlow\Http\Response
    */
   protected function redirectBack(): Response
   {
      return Redirect::back();
   }

   /**
    * Redirige vers l'URL prévue ou une URL par défaut
    *
    * @param string $default 
    * @return \IronFlow\Http\Response
    */
   protected function redirectIntended(string $default = '/'): Response
   {
      return Redirect::intended($default);
   }

   /**
    * Afficher les données json
    *
    * @param array $data
    * @param int $status
    * @return \IronFlow\Http\Response
    */
   protected function json(array $data, int $status = 200): Response
   {
      return Response::json($data, $status);
   }

   /**
    * Rediriger vers une route
    *
    * @param string $name
    * @param array $parameters
    * @return \IronFlow\Http\Response
    */
   public function route(string $name, array $parameters = []): Response
   {
      $router = Application::getInstance()->getRouter();
      $url = $router->generateUrl($name, $parameters);
      return $this->redirect($url);
   }

   /**
    * Déclencher une erreur
    *
    * @param int $status
    * @param string $message
    * @throws \IronFlow\Http\Exceptions\HttpException
    * @return never
    */
   protected function abort(int $status = 404, string $message = "Page non trouvée", array $context = []): never
   {
      throw new HttpException($status, $message, $context);
   }

   /**
    * Verifier l'accès au controller
    *
    * @param array $abilities
    * @param Model $model
    * @return bool
    */
   protected function authorize(array $abilities, Model $model): bool
    {
      foreach ($abilities as $ability) {
         if (!Auth::can($ability, $model)) {
            return false;
        }
      }

      return true;
    }

   /**
    * Retourner une reponse Http
    *
    * @return \IronFlow\Http\Response
    */
   protected function response(): Response
   {
      return $this->response;
   }

   /**
    * Rediriger vers la precedente URL
    *
    * @return \IronFlow\Http\Response
    */
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
      return $validator->passes() ? true : $validator->errors();
   }

   /**
    * Ajouter les middlewares
    *
    * @param string|array $middleware
    * @return Controller
    */
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
