<?php

declare(strict_types=1);

namespace App\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;

class DashboardController extends Controller
{
   public function index(Request $request): Response
   {
      return $this->view('dashboard.index', [
         'title' => 'Tableau de bord'
      ]);
   }
}
