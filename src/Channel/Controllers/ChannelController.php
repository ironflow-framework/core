<?php

declare(strict_types=1);

namespace IronFlow\Channel\Controllers;

use IronFlow\Http\Controllers\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Channel\Events\ChannelEvent;
use IronFlow\Channel\ChannelManager;
use IronFlow\Support\Facades\Auth;

/**
 * Contrôleur pour la gestion des Channels
 */
class ChannelController extends Controller
{
   /**
    * Instance du gestionnaire de channels
    */
   protected ChannelManager $channelManager;

   /**
    * Constructeur
    *
    * @param ChannelManager $channelManager
    */
   public function __construct(ChannelManager $channelManager)
   {
      $this->channelManager = $channelManager;
   }

   /**
    * Authentifie une connexion à un channel
    *
    * @param Request $request
    * @return Response
    */
   public function auth(Request $request): Response
   {
      $channelName = $request->post('channel_name');
      $socketId = $request->post('socket_id');

      if (empty($channelName) || empty($socketId)) {
         return $this->jsonResponse([
            'error' => 'Les paramètres channel_name et socket_id sont requis'
         ], 422);
      }

      // Vérifier si l'utilisateur est authentifié
      if (!Auth::check()) {
         return $this->jsonResponse([
            'error' => 'Non autorisé'
         ], 401);
      }

      $userId = (string) Auth::id();

      // Récupérer ou créer le channel
      $channel = $this->channelManager->getChannel($channelName);
      if (!$channel) {
         $channel = $this->channelManager->createChannel($channelName);
      }

      // Vérifier l'autorisation
      if (!$channel->authorize($userId)) {
         return $this->jsonResponse([
            'error' => 'Non autorisé pour ce channel'
         ], 403);
      }

      // Générer un token d'authentification (simplifié ici)
      $auth = [
         'channel' => $channelName,
         'socket_id' => $socketId,
         'user_id' => $userId,
         'timestamp' => time()
      ];

      $token = base64_encode(json_encode($auth));

      return $this->jsonResponse([
         'auth' => $token
      ]);
   }

   /**
    * S'abonne à un channel
    *
    * @param Request $request
    * @return Response
    */
   public function subscribe(Request $request): Response
   {
      $channelName = $request->post('channel');

      if (empty($channelName)) {
         return $this->jsonResponse([
            'error' => 'Le paramètre channel est requis'
         ], 422);
      }

      if (!Auth::check()) {
         return $this->jsonResponse([
            'error' => 'Non autorisé'
         ], 401);
      }

      $userId = (string) Auth::id();

      $success = $this->channelManager->subscribe($channelName, $userId);

      return $this->jsonResponse([
         'success' => $success
      ]);
   }

   /**
    * Se désabonne d'un channel
    *
    * @param Request $request
    * @return Response
    */
   public function unsubscribe(Request $request): Response
   {
      $channelName = $request->post('channel');

      if (empty($channelName)) {
         return $this->jsonResponse([
            'error' => 'Le paramètre channel est requis'
         ], 422);
      }

      if (!Auth::check()) {
         return $this->jsonResponse([
            'error' => 'Non autorisé'
         ], 401);
      }

      $userId = (string) Auth::id();

      $success = $this->channelManager->unsubscribe($channelName, $userId);

      return $this->jsonResponse([
         'success' => $success
      ]);
   }

   /**
    * Diffuse un événement sur un channel
    *
    * @param Request $request
    * @return Response
    */
   public function broadcast(Request $request): Response
   {
      $channelName = $request->post('channel');
      $eventName = $request->post('event');
      $data = $request->post('data', []);

      if (empty($channelName) || empty($eventName)) {
         return $this->jsonResponse([
            'error' => 'Les paramètres channel et event sont requis'
         ], 422);
      }

      if (!Auth::check()) {
         return $this->jsonResponse([
            'error' => 'Non autorisé'
         ], 401);
      }

      $userId = (string) Auth::id();

      // Créer l'événement
      $event = new ChannelEvent($eventName, $data, $userId);

      // Diffuser l'événement
      $success = $this->channelManager->broadcast($channelName, $event);

      return $this->jsonResponse([
         'success' => $success
      ]);
   }

   /**
    * Retourne une réponse JSON
    *
    * @param array $data
    * @param int $status
    * @return Response
    */
   protected function jsonResponse(array $data, int $status = 200): Response
   {
      return new Response(
         json_encode($data),
         $status,
         ['Content-Type' => 'application/json']
      );
   }
}
