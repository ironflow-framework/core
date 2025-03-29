<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Controllers;

use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\CraftPanel\Models\AdminActivityLog;

/**
 * Contrôleur pour le tableau de bord du CraftPanel
 */
class DashboardController extends CraftPanelController
{
   /**
    * Affiche le tableau de bord
    *
    * @param Request $request
    * @return Response
    */
   public function dashboard(Request $request): Response
   {
      // Récupération des statistiques et des activités récentes
      $stats = $this->getDashboardStats();
      $recentActivities = AdminActivityLog::latest()->limit(10)->get();

      return $this->view('craftpanel::dashboard.index', [
         'stats' => $stats,
         'recentActivities' => $recentActivities
      ]);
   }

   /**
    * Récupère les statistiques pour le tableau de bord
    *
    * @return array
    */
   private function getDashboardStats(): array
   {
      // Exemple de statistiques, à adapter selon les besoins réels
      return [
         'userCount' => \IronFlow\CraftPanel\Models\AdminUser::count(),
         'activityCount' => AdminActivityLog::count(),
         'lastLoginDate' => AdminActivityLog::where('action', 'login')->latest()->first()?->created_at,
         'systemVersion' => config('app.version', '1.0.0'),
      ];
   }
}
