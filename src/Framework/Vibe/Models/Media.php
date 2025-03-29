<?php

declare(strict_types=1);

namespace IronFlow\Vibe\Models;

use IronFlow\Database\Model;
use IronFlow\Support\Facades\Storage;
use IronFlow\Vibe\MediaManager;

class Media extends Model
{
   /**
    * Table associée au modèle
    *
    * @var string
    */
   protected $table = 'media';

   /**
    * Attributs pouvant être assignés en masse
    *
    * @var array
    */
   protected $fillable = [
      'name',
      'filename',
      'path',
      'mime_type',
      'size',
      'disk',
      'extension',
      'type',
      'metadata',
      'title',
      'alt',
      'description',
      'model_type',
      'model_id',
   ];

   /**
    * Attributs qui doivent être convertis en types natifs
    *
    * @var array
    */
   protected $casts = [
      'size' => 'integer',
      'metadata' => 'array',
   ];

   /**
    * Obtient l'URL du média
    *
    * @param string|null $size Pour les images uniquement (thumbnail, small, medium, large)
    * @return string
    */
   public function getUrl(?string $size = null): string
   {
      return MediaManager::instance()->getUrl($this, $size);
   }

   /**
    * Obtient le chemin complet du fichier
    *
    * @return string
    */
   public function getFullPath(): string
   {
      $disk = config("vibe.disks.{$this->disk}", []);
      return ($disk['root'] ?? '') . '/' . $this->path;
   }

   /**
    * Détermine si le média est une image
    *
    * @return bool
    */
   public function isImage(): bool
   {
      return $this->type === 'image';
   }

   /**
    * Détermine si le média est un document
    *
    * @return bool
    */
   public function isDocument(): bool
   {
      return $this->type === 'document';
   }

   /**
    * Détermine si le média est une vidéo
    *
    * @return bool
    */
   public function isVideo(): bool
   {
      return $this->type === 'video';
   }

   /**
    * Détermine si le média est un fichier audio
    *
    * @return bool
    */
   public function isAudio(): bool
   {
      return $this->type === 'audio';
   }

   /**
    * Détermine si le média est une archive
    *
    * @return bool
    */
   public function isArchive(): bool
   {
      return $this->type === 'archive';
   }

   /**
    * Récupère la taille du fichier formatée de manière lisible
    *
    * @return string
    */
   public function getFormattedSizeAttribute(): string
   {
      $units = ['B', 'KB', 'MB', 'GB', 'TB'];

      $bytes = max($this->size, 0);
      $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
      $pow = min($pow, count($units) - 1);

      $bytes /= (1 << (10 * $pow));

      return round($bytes, 2) . ' ' . $units[$pow];
   }

   /**
    * Relation polymorphique avec d'autres modèles
    *
    * @return \IronFlow\Database\Relations\MorphTo
    */
   public function model()
   {
      return $this->morphTo();
   }

   /**
    * Supprime le média et le fichier associé
    *
    * @return bool|null
    * @throws \Exception
    */
   public function delete()
   {
      MediaManager::instance()->delete($this);
      return parent::delete();
   }
}
