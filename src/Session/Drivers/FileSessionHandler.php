<?php

declare(strict_types=1);

namespace IronFlow\Session\Drivers;

use SessionHandlerInterface;

/**
 * Gestionnaire de session basé sur les fichiers
 */
class FileSessionHandler implements SessionHandlerInterface
{
   /**
    * Chemin du répertoire de stockage des sessions
    */
   private string $path;

   /**
    * Constructeur
    *
    * @param string $path Chemin du répertoire de stockage
    */
   public function __construct(string $path)
   {
      $this->path = $path;
      if (!is_dir($path)) {
         mkdir($path, 0777, true);
      }
   }

   /**
    * {@inheritdoc}
    */
   public function open(string $path, string $name): bool
   {
      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function close(): bool
   {
      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function read(string $id): string|false
   {
      $file = $this->path . '/sess_' . $id;
      if (!file_exists($file)) {
         return false;
      }
      return (string) file_get_contents($file);
   }

   /**
    * {@inheritdoc}
    */
   public function write(string $id, string $data): bool
   {
      $file = $this->path . '/sess_' . $id;
      return file_put_contents($file, $data) !== false;
   }

   /**
    * {@inheritdoc}
    */
   public function destroy(string $id): bool
   {
      $file = $this->path . '/sess_' . $id;
      if (file_exists($file)) {
         return unlink($file);
      }
      return true;
   }

   /**
    * {@inheritdoc}
    */
   public function gc(int $max_lifetime): int|false
   {
      $count = 0;
      foreach (glob($this->path . '/sess_*') as $file) {
         if (filemtime($file) + $max_lifetime < time() && unlink($file)) {
            $count++;
         }
      }
      return $count;
   }
}
