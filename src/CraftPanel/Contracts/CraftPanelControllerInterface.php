<?php

declare(strict_types=1);

namespace IronFlow\CraftPanel\Contracts;

use IronFlow\Http\Request;
use IronFlow\Http\Response;

/**
 * Interface pour les contrôleurs du CraftPanel
 */
interface CraftPanelControllerInterface
{
   /**
    * Méthode pour l'affichage du tableau de bord
    *
    * @param Request $request
    * @return Response
    */
   public function dashboard(Request $request): Response;
}
