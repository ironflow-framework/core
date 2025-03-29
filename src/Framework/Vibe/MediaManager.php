<?php

declare(strict_types=1);

namespace IronFlow\Vibe;

use IronFlow\Support\Facades\Storage;
use IronFlow\Vibe\Exceptions\MediaException;
use IronFlow\Vibe\Models\Media;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaManager
{
   /**
    * Instance unique de MediaManager (singleton)
    *
    * @var self|null
    */
   private static ?self $instance = null;

   /**
    * Configuration de Vibe
    *
    * @var array
    */
   protected array $config;

   /**
    * Constructeur privé pour le singleton
    */
   private function __construct()
   {
      $this->config = config('vibe', []);
   }

   /**
    * Obtient l'instance unique du MediaManager
    *
    * @return self
    */
   public static function instance(): self
   {
      if (self::$instance === null) {
         self::$instance = new self();
      }

      return self::$instance;
   }

   /**
    * Télécharge et enregistre un fichier
    *
    * @param UploadedFile $file Le fichier téléchargé
    * @param string|null $disk Le disque de stockage
    * @param array $attributes Attributs additionnels
    * @return Media
    * @throws MediaException
    */
   public function upload(UploadedFile $file, ?string $disk = null, array $attributes = []): Media
   {
      // Validation du fichier
      $this->validateFile($file);

      // Détermine le disque de stockage
      $disk = $disk ?? $this->config['default_disk'] ?? 'public';

      // Prépare les informations de base
      $originalName = $file->getClientOriginalName();
      $extension = $file->getClientOriginalExtension();
      $mimeType = $file->getMimeType() ?? 'application/octet-stream';
      $size = $file->getSize();

      // Génère un nom de fichier unique
      $filename = md5($originalName . time()) . '.' . $extension;

      // Détermine le chemin de stockage
      $path = date('Y/m') . '/' . $filename;

      // Détermine le type de média
      $type = $this->getMediaType($extension, $mimeType);

      // Stocke le fichier
      try {
         Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));
      } catch (\Exception $e) {
         throw new MediaException("Impossible de stocker le fichier: " . $e->getMessage());
      }

      // Extrait les métadonnées
      $metadata = $this->extractMetadata($file, $type);

      // Crée la miniature pour les images
      if ($type === 'image') {
         $this->createThumbnails($file, $disk, $path);
      }

      // Crée l'enregistrement dans la base de données
      $media = new Media([
         'name' => $originalName,
         'filename' => $filename,
         'path' => $path,
         'mime_type' => $mimeType,
         'size' => $size,
         'disk' => $disk,
         'extension' => $extension,
         'type' => $type,
         'metadata' => $metadata,
      ]);

      // Ajoute les attributs supplémentaires
      foreach ($attributes as $key => $value) {
         if (property_exists($media, $key) || in_array($key, $media->fillable ?? [])) {
            $media->{$key} = $value;
         }
      }

      $media->save();

      return $media;
   }

   /**
    * Supprime un média et son fichier associé
    *
    * @param Media $media
    * @return bool
    * @throws MediaException
    */
   public function delete(Media $media): bool
   {
      try {
         // Supprime le fichier principal
         Storage::disk($media->disk)->delete($media->path);

         // Supprime les miniatures si c'est une image
         if ($media->isImage()) {
            $this->deleteThumbnails($media);
         }

         return true;
      } catch (\Exception $e) {
         throw new MediaException("Impossible de supprimer le fichier: " . $e->getMessage());
      }
   }

   /**
    * Obtient l'URL d'un média
    *
    * @param Media $media
    * @param string|null $size Taille pour les images (thumbnail, small, medium, large)
    * @return string
    */
   public function getUrl(Media $media, ?string $size = null): string
   {
      $disk = $media->disk;
      $path = $media->path;

      // Si une taille est spécifiée et que c'est une image, utilise la miniature
      if ($size !== null && $media->isImage() && in_array($size, array_keys($this->config['image_sizes'] ?? []))) {
         $pathInfo = pathinfo($path);
         $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];

         if (Storage::disk($disk)->exists($thumbnailPath)) {
            $path = $thumbnailPath;
         }
      }

      // Récupère la configuration du disque
      $diskConfig = $this->config['disks'][$disk] ?? null;

      if ($diskConfig && isset($diskConfig['url'])) {
         return rtrim($diskConfig['url'], '/') . '/' . $path;
      }

      // Si le disque est public, utilise l'URL publique
      if ($disk === 'public') {
         return \url('storage/' . $path);
      }

      // Sinon, retourne une URL qui passera par un contrôleur
      return \url('media/' . $media->id);
   }

   /**
    * Valide un fichier téléchargé
    *
    * @param UploadedFile $file
    * @return bool
    * @throws MediaException
    */
   protected function validateFile(UploadedFile $file): bool
   {
      // Vérifie si le fichier est valide
      if (!$file->isValid()) {
         throw new MediaException("Le fichier n'est pas valide: " . $file->getErrorMessage());
      }

      // Vérifie la taille du fichier
      $maxSize = $this->config['max_file_size'] ?? (100 * 1024 * 1024); // 100 MB par défaut
      if ($file->getSize() > $maxSize) {
         throw new MediaException("Le fichier est trop volumineux. Taille maximale: " . ($maxSize / 1024 / 1024) . " MB");
      }

      // Vérifie l'extension du fichier
      $extension = strtolower($file->getClientOriginalExtension());
      $allowedTypes = $this->config['allowed_types'] ?? [];
      $allowedExtensions = [];

      foreach ($allowedTypes as $extensions) {
         $allowedExtensions = array_merge($allowedExtensions, $extensions);
      }

      if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
         throw new MediaException("Extension de fichier non autorisée: " . $extension);
      }

      return true;
   }

   /**
    * Détermine le type de média à partir de l'extension et du type MIME
    *
    * @param string $extension
    * @param string $mimeType
    * @return string
    */
   protected function getMediaType(string $extension, string $mimeType): string
   {
      $extension = strtolower($extension);
      $allowedTypes = $this->config['allowed_types'] ?? [];

      foreach ($allowedTypes as $type => $extensions) {
         if (in_array($extension, $extensions)) {
            return $type;
         }
      }

      // Détection par le type MIME
      if (strpos($mimeType, 'image/') === 0) {
         return 'image';
      }

      if (strpos($mimeType, 'video/') === 0) {
         return 'video';
      }

      if (strpos($mimeType, 'audio/') === 0) {
         return 'audio';
      }

      if (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
         return 'document';
      }

      if (in_array($mimeType, ['application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed'])) {
         return 'archive';
      }

      return 'other';
   }

   /**
    * Extrait les métadonnées d'un fichier
    *
    * @param UploadedFile $file
    * @param string $type
    * @return array
    */
   protected function extractMetadata(UploadedFile $file, string $type): array
   {
      $metadata = [];

      switch ($type) {
         case 'image':
            if (class_exists(Image::class)) {
               try {
                  $image = Image::make($file->getRealPath());
                  $metadata = [
                     'width' => $image->width(),
                     'height' => $image->height(),
                     'ratio' => $image->width() / $image->height(),
                  ];

                  // Extraction des données EXIF si disponibles
                  if (function_exists('exif_read_data') && $file->getMimeType() === 'image/jpeg') {
                     $exif = @exif_read_data($file->getRealPath());
                     if ($exif) {
                        $metadata['exif'] = array_intersect_key($exif, array_flip([
                           'Make',
                           'Model',
                           'DateTimeOriginal',
                           'ExposureTime',
                           'FNumber',
                           'ISOSpeedRatings',
                           'FocalLength',
                           'GPSLatitude',
                           'GPSLongitude'
                        ]));
                     }
                  }
               } catch (\Exception $e) {
                  // Ignore les erreurs d'extraction des métadonnées
               }
            }
            break;

         case 'video':
            // Extraction des métadonnées vidéo si ffprobe est disponible
            if (function_exists('shell_exec') && $this->commandExists('ffprobe')) {
               try {
                  $cmd = "ffprobe -v quiet -print_format json -show_format -show_streams " . escapeshellarg($file->getRealPath());
                  $output = shell_exec($cmd);

                  if ($output) {
                     $videoInfo = json_decode($output, true);
                     if (is_array($videoInfo) && isset($videoInfo['format'])) {
                        $metadata = [
                           'duration' => $videoInfo['format']['duration'] ?? null,
                           'bitrate' => $videoInfo['format']['bit_rate'] ?? null,
                           'format' => $videoInfo['format']['format_name'] ?? null,
                        ];

                        foreach ($videoInfo['streams'] ?? [] as $stream) {
                           if (($stream['codec_type'] ?? '') === 'video') {
                              $metadata['width'] = $stream['width'] ?? null;
                              $metadata['height'] = $stream['height'] ?? null;
                              $metadata['codec'] = $stream['codec_name'] ?? null;
                              break;
                           }
                        }
                     }
                  }
               } catch (\Exception $e) {
                  // Ignore les erreurs d'extraction des métadonnées
               }
            }
            break;

         case 'audio':
            // Extraction des métadonnées audio si ffprobe est disponible
            if (function_exists('shell_exec') && $this->commandExists('ffprobe')) {
               try {
                  $cmd = "ffprobe -v quiet -print_format json -show_format " . escapeshellarg($file->getRealPath());
                  $output = shell_exec($cmd);

                  if ($output) {
                     $audioInfo = json_decode($output, true);
                     if (is_array($audioInfo) && isset($audioInfo['format'])) {
                        $metadata = [
                           'duration' => $audioInfo['format']['duration'] ?? null,
                           'bitrate' => $audioInfo['format']['bit_rate'] ?? null,
                           'format' => $audioInfo['format']['format_name'] ?? null,
                        ];

                        if (isset($audioInfo['format']['tags'])) {
                           $tags = $audioInfo['format']['tags'];
                           $metadata['title'] = $tags['title'] ?? null;
                           $metadata['artist'] = $tags['artist'] ?? null;
                           $metadata['album'] = $tags['album'] ?? null;
                           $metadata['year'] = $tags['date'] ?? null;
                           $metadata['genre'] = $tags['genre'] ?? null;
                        }
                     }
                  }
               } catch (\Exception $e) {
                  // Ignore les erreurs d'extraction des métadonnées
               }
            }
            break;
      }

      return $metadata;
   }

   /**
    * Crée des miniatures pour les images
    *
    * @param UploadedFile $file
    * @param string $disk
    * @param string $path
    * @return void
    */
   protected function createThumbnails(UploadedFile $file, string $disk, string $path): void
   {
      if (!class_exists(Image::class)) {
         return;
      }

      $imageSizes = $this->config['image_sizes'] ?? [];
      $quality = $this->config['image_quality'] ?? 85;

      try {
         $image = Image::make($file->getRealPath());
         $pathInfo = pathinfo($path);

         foreach ($imageSizes as $size => $dimensions) {
            list($width, $height) = $dimensions;

            // Crée un nom de fichier pour la miniature
            $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];

            // Redimensionne l'image
            $resized = $image->fit($width, $height, function ($constraint) {
               $constraint->aspectRatio();
               $constraint->upsize();
            });

            // Sauvegarde la miniature
            Storage::disk($disk)->put($thumbnailPath, (string) $resized->encode($pathInfo['extension'], $quality));
         }
      } catch (\Exception $e) {
         // Ignore les erreurs de création de miniatures
      }
   }

   /**
    * Supprime les miniatures d'une image
    *
    * @param Media $media
    * @return void
    */
   protected function deleteThumbnails(Media $media): void
   {
      if (!$media->isImage()) {
         return;
      }

      $imageSizes = $this->config['image_sizes'] ?? [];
      $disk = $media->disk;
      $pathInfo = pathinfo($media->path);

      foreach (array_keys($imageSizes) as $size) {
         $thumbnailPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_' . $size . '.' . $pathInfo['extension'];

         if (Storage::disk($disk)->exists($thumbnailPath)) {
            Storage::disk($disk)->delete($thumbnailPath);
         }
      }
   }

   /**
    * Vérifie si une commande shell existe
    *
    * @param string $command
    * @return bool
    */
   protected function commandExists(string $command): bool
   {
      if (!function_exists('shell_exec')) {
         return false;
      }

      $checkCommand = PHP_OS_FAMILY === 'Windows'
         ? "where $command 2>nul"
         : "command -v $command 2>/dev/null";

      $output = shell_exec($checkCommand);

      return !empty($output);
   }
}
