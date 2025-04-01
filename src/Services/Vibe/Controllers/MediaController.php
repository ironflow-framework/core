<?php

declare(strict_types=1);

namespace IronFlow\Services\Vibe\Controllers;

use IronFlow\Http\Controller;
use IronFlow\Http\Request;
use IronFlow\Http\Response;
use IronFlow\Services\Vibe\Models\Media;
use IronFlow\Services\Vibe\Exceptions\MediaException;

use IronFlow\Services\Vibe\MediaManager;

class MediaController extends Controller
{
   /**
    * Affiche une liste des médias
    *
    * @param Request $request
    * @return Response
    */
   public function index(Request $request): Response
   {
      $media = Media::all();
      return Response::view('vibe.media.index', ['media' => $media]);
   }

   /**
    * Affiche un formulaire pour téléverser un média
    *
    * @param Request $request
    * @return Response
    */
   public function create(Request $request): Response
   {
      return Response::view('vibe.media.create');
   }

   /**
    * Stocke un nouveau média
    *
    * @param Request $request
    * @return Response
    */
   public function store(Request $request): Response
   {
      if (!$request->hasFile('file')) {
         return Response::json(['error' => 'Aucun fichier n\'a été téléversé'], 400);
      }

      try {
         $file = $request->file('file');
         $media = MediaManager::instance()->upload($file);

         return Response::json([
            'success' => true,
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->name,
            'type' => $media->type,
            'size' => $media->getFormattedSizeAttribute(),
         ]);
      } catch (MediaException $e) {
         return Response::json(['error' => $e->getMessage()], 400);
      } catch (\Exception $e) {
         return Response::json(['error' => 'Une erreur s\'est produite lors du téléversement'], 500);
      }
   }

   /**
    * Affiche un média spécifique
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function show(Request $request, int $id): Response
   {
      $media = Media::find($id);

      if (!$media) {
         return Response::notFound('Média non trouvé');
      }

      return Response::view('vibe.media.show', ['media' => $media]);
   }

   /**
    * Supprime un média
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function destroy(Request $request, int $id): Response
   {
      $media = Media::find($id);

      if (!$media) {
         return Response::json(['error' => 'Média non trouvé'], 404);
      }

      try {
         MediaManager::instance()->delete($media);
         $media->delete();

         return Response::json(['success' => true]);
      } catch (\Exception $e) {
         return Response::json(['error' => 'Une erreur s\'est produite lors de la suppression'], 500);
      }
   }

   /**
    * Télécharge un média
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function download(Request $request, int $id): Response
   {
      $media = Media::find($id);

      if (!$media) {
         return Response::notFound('Média non trouvé');
      }

      try {
         return Response::download($media->getFullPath(), $media->name);
      } catch (\Exception $e) {
         return Response::serverError('Erreur lors du téléchargement du fichier');
      }
   }

   /**
    * Sert un média (pour les fichiers privés)
    *
    * @param Request $request
    * @param int $id
    * @return Response
    */
   public function serve(Request $request, int $id): Response
   {
      $media = Media::find($id);

      if (!$media) {
         return Response::notFound('Média non trouvé');
      }

      try {
         return Response::file($media->getFullPath(), $media->mime_type);
      } catch (\Exception $e) {
         return Response::serverError('Erreur lors de l\'affichage du fichier');
      }
   }
}
