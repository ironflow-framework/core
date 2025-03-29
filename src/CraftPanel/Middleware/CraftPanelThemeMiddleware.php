<?php

namespace IronFlow\CraftPanel\Middleware;

use IronFlow\Http\Middleware;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Support\Config;

class CraftPanelThemeMiddleware extends Middleware
{
   /**
    * Handle an incoming request.
    *
    * @param  \IronFlow\Http\Request  $request
    * @param  callable  $next
    * @return \IronFlow\Http\Response
    */
   public function handle(Request $request, callable $next): Response
   {
      // Récupérer le thème actuel
      $theme = session()->get('craftpanel_theme', Config::get('craftpanel.theme.default', 'light'));

      // Changer le thème si demandé
      if ($request->has('theme') && in_array($request->get('theme'), Config::get('craftpanel.theme.options', ['light', 'dark']))) {
         $theme = $request->get('theme');
         session()->put('craftpanel_theme', $theme);
      }

      // Stocker le thème pour les vues
      $request->attributes->set('craftpanel_theme', $theme);

      return $next($request);
   }
}
