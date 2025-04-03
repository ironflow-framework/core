<?php

declare(strict_types=1);

namespace IronFlow\Core\Exceptions;

use IronFlow\Core\Application\Application;
use IronFlow\Http\Response;
use IronFlow\Support\Facades\Config;

/**
 * Classe ExceptionHandler
 * 
 * Gère les exceptions de manière centralisée
 */
class ExceptionHandler
{
   private Application $app;

   /**
    * Constructeur
    * 
    * @param Application $app
    */
   public function __construct(Application $app)
   {
      $this->app = $app;
   }

   /**
    * Gère une exception
    * 
    * @param \Throwable $e
    * @return Response
    */
   public function handle(\Throwable $e): Response
   {
      if (Config::get('app.debug', false)) {
         return Response::view('errors/debug', [
            'exception' => $e,
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
         ], 500);
      }

      return Response::view('errors/500', [], 500);
   }
}
