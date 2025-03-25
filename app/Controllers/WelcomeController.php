<?php

namespace App\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Response;

class WelcomeController extends Controller
{
   public function index(): Response
   {
      return $this->view('welcome');
   }
}
