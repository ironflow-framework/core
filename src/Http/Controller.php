<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\View\ViewInterface;
use IronFlow\Http\Response;
use IronFlow\View\TwigView;
use IronFlow\Validation\Validator;

abstract class Controller
{
   protected TwigView $view;
   protected Response $response;

   public function __construct(?ViewInterface $view = null)
   {
      $this->view = $view ?? new TwigView(dirname(__DIR__, 2) . '/resources/views');
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

   protected function redirect(?string $url = null)
   {
      if ($url) {
         return $this->response
            ->setStatusCode(302)
            ->setHeader('Location', $url);
      }
      return $this;
   }

   public function route(string $name, array $parameters = []): self
   {
      // TODO
      return $this;
   }

   public function with(string $key, mixed $value): self
   {
      $this->response->with($key, $value);
      return $this;
   }

   protected function abort(int $status, string $message)
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
      return $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
   }

   protected function validate(array $data, array $rules): bool
   {
      $validator = new Validator($data, $rules);
      return $validator->validate();
   }
}
