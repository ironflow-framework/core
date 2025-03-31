<?php

namespace App\Http\Controllers\CraftPanel;

use App\Http\Controllers\Controller;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\View;

class DashboardController extends Controller
{
   /**
    * Affiche le tableau de bord du CraftPanel
    *
    * @return \IronFlow\Support\Facades\View
    */
   public function index()
   {
      $user = Auth::guard('craftpanel')->user();
      $stats = $this->getDashboardStats();

      return View::make('craftpanel.dashboard', [
         'user' => $user,
         'stats' => $stats,
      ]);
   }

   /**
    * Récupère les statistiques pour le tableau de bord
    *
    * @return array
    */
   protected function getDashboardStats()
   {
      return [
         'total_users' => \App\Models\User::count(),
         'active_users' => \App\Models\User::where('is_active', true)->count(),
         'total_roles' => \App\Models\Role::count(),
         'system_status' => $this->getSystemStatus(),
      ];
   }

   /**
    * Récupère l'état du système
    *
    * @return array
    */
   protected function getSystemStatus()
   {
      return [
         'php_version' => PHP_VERSION,
         'memory_usage' => memory_get_usage(true),
         'disk_free_space' => disk_free_space('/'),
         'uptime' => $this->getUptime(),
      ];
   }

   /**
    * Récupère le temps de fonctionnement du système
    *
    * @return string
    */
   protected function getUptime()
   {
      if (function_exists('exec')) {
         if (PHP_OS == 'Linux') {
            exec('uptime -p', $output);
            return $output[0] ?? 'N/A';
         }
      }
      return 'N/A';
   }
}
