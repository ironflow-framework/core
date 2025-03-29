<?php

return [
   /*
    |--------------------------------------------------------------------------
    | Configuration du système de gestion de médias Vibe
    |--------------------------------------------------------------------------
    |
    | Cette configuration définit les paramètres du système de gestion
    | de médias Vibe, notamment les disques de stockage, les types
    | de fichiers autorisés, les tailles d'image, etc.
    |
    */

   // Disque de stockage par défaut
   'default_disk' => env('VIBE_DEFAULT_DISK', 'public'),

   // Disques de stockage disponibles
   'disks' => [
      'public' => [
         'driver' => 'local',
         'root' => storage_path('app/public'),
         'url' => env('APP_URL') . '/storage',
         'visibility' => 'public',
      ],
      'local' => [
         'driver' => 'local',
         'root' => storage_path('app'),
         'visibility' => 'private',
      ],
      // Vous pouvez ajouter d'autres disques ici (S3, FTP, etc.)
   ],

   // Types de fichiers autorisés par catégorie
   'allowed_types' => [
      'image' => ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'],
      'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'],
      'video' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm'],
      'audio' => ['mp3', 'wav', 'ogg', 'aac', 'flac'],
      'archive' => ['zip', 'rar', '7z', 'tar', 'gz'],
   ],

   // Taille maximale des fichiers (en octets)
   'max_file_size' => env('VIBE_MAX_FILE_SIZE', 100 * 1024 * 1024), // 100 MB par défaut

   // Tailles d'image prédéfinies
   'image_sizes' => [
      'thumbnail' => [150, 150],
      'small' => [300, 300],
      'medium' => [600, 600],
      'large' => [1200, 1200],
   ],

   // Qualité de compression des images redimensionnées (1-100)
   'image_quality' => 85,

   // Configuration du composant FileUploader
   'uploader' => [
      'use_dropzone' => true,
      'dropzone_cdn' => 'https://unpkg.com/dropzone@5/dist/min/dropzone.min.css',
      'dropzone_js_cdn' => 'https://unpkg.com/dropzone@5/dist/min/dropzone.min.js',
   ],

   // Configuration du lecteur média
   'player' => [
      'default_video_width' => '100%',
      'default_video_height' => 'auto',
      'show_controls' => true,
      'use_plyr' => true,
      'plyr_cdn' => 'https://cdn.plyr.io/3.6.8/plyr.css',
      'plyr_js_cdn' => 'https://cdn.plyr.io/3.6.8/plyr.js',
   ],
];
