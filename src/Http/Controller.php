<?php

declare(strict_types=1);

namespace IronFlow\Http;

use IronFlow\View\ViewInterface;
use IronFlow\Http\Response;
use IronFlow\View\TwigView;

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

   protected function redirect(string $url): Response
   {
      return $this->response
         ->setStatusCode(302)
         ->setHeader('Location', $url);
   }

   protected function back(): Response
   {
      return $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
   }

   protected function validate(array $data, array $rules): array
   {
      // TODO: Implémenter la validation des données
      return [];
   }
}
