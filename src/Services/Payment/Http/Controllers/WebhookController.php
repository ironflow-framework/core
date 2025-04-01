<?php

declare(strict_types=1);

namespace IronFlow\Services\Payment\Http\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Services\Payment\Facades\Payment;
use IronFlow\Support\Facades\Log;
use IronFlow\Services\Payment\Exceptions\PaymentException;

/**
 * Contrôleur pour gérer les webhooks de paiement
 */
class WebhookController extends Controller
{
   /**
    * Traite un webhook entrant
    */
   public function handleWebhook(Request $request, string $provider): Response
   {
      try {
         // Récupérer le contenu brut du webhook
         $payload = $request->getContent();
         $headers = $request->headers->all();

         // Journaliser la réception du webhook
         Log::info("Webhook reçu pour le provider $provider", [
            'provider' => $provider,
            'method' => $request->getMethod(),
            'headers' => $headers,
         ]);

         // Traiter le webhook
         $result = Payment::handleWebhook($payload, $headers, $provider);

         // Journaliser le succès
         Log::info("Webhook traité avec succès", [
            'provider' => $provider,
            'result' => $result,
         ]);

         return $this->json([
            'status' => 'success',
            'message' => 'Webhook traité avec succès',
            'data' => $result,
         ]);
      } catch (PaymentException $e)    {
         // Journaliser l'erreur
         Log::error("Erreur lors du traitement du webhook", [
            'provider' => $provider,
            'error' => $e->getMessage(),
            'context' => $e->getContext(),
         ]);

         return $this->json([
            'status' => 'error',
            'message' => $e->getMessage(),
         ], 400);
      } catch (\Throwable $e) {
         // Journaliser l'erreur
         Log::error("Erreur inattendue lors du traitement du webhook", [
            'provider' => $provider,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
         ]);

         return $this->json([
            'status' => 'error',
            'message' => 'Erreur inattendue lors du traitement du webhook',
         ], 500);
      }
   }

   /**
    * Page de succès après un paiement
    */
   public function success(Request $request): Response
   {
      $sessionId = $request->query->get('session_id');
      $paymentIntentId = $request->query->get('payment_intent');

      return $this->view('payment.success', [
         'session_id' => $sessionId,
         'payment_intent_id' => $paymentIntentId,
      ]);
   }

   /**
    * Page d'annulation après un paiement
    */
   public function cancel(Request $request): Response
   {
      $sessionId = $request->query->get('session_id');

      return $this->view('payment.cancel', [
         'session_id' => $sessionId,
      ]);
   }
}
