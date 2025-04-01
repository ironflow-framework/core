<?php

namespace App\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Response;
use IronFlow\Core\Application;

class WelcomeController extends Controller
{
   public function index(): Response
   {
      return $this->view('welcome', [
         'APP_VERSION' => Application::VERSION
      ]);
   }
}
