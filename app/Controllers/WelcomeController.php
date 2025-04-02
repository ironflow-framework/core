<?php

namespace App\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Response\Response;
use IronFlow\Core\Application\Application;

class WelcomeController extends Controller
{
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
