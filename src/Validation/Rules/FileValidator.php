<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\Validator;

class FileValidator extends Validator
{
   protected array $allowedMimeTypes = [];
   protected int $maxSize = 0;
   protected array $allowedExtensions = [];
   protected bool $required = false;

   public function required(bool $required = true): self
   {
      $this->required = $required;
      return $this;
   }

   public function mimeTypes(array $types): self
   {
      $this->allowedMimeTypes = $types;
      return $this;
   }

   public function maxSize(int $size): self
   {
      $this->maxSize = $size;
      return $this;
   }

   public function extensions(array $extensions): self
   {
      $this->allowedExtensions = $extensions;
      return $this;
   }

   public function validate($value, array $data = []): bool
   {
      if (empty($value)) {
         return !$this->required;
      }

      if (!is_array($value)) {
         $value = [$value];
      }

      foreach ($value as $file) {
         if (!$this->validateFile($file)) {
            return false;
         }
      }

      return true;
   }

   protected function validateFile($file): bool
   {
      if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
         $this->addError('Le fichier n\'a pas été correctement uploadé.', 'upload');
         return false;
      }

      if ($this->maxSize > 0 && $file['size'] > $this->maxSize) {
         $this->addError(sprintf('Le fichier ne doit pas dépasser %d octets.', $this->maxSize), 'size');
         return false;
      }

      if (!empty($this->allowedMimeTypes)) {
         $finfo = finfo_open(FILEINFO_MIME_TYPE);
         $mimeType = finfo_file($finfo, $file['tmp_name']);
         finfo_close($finfo);

         if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $this->addError('Le type de fichier n\'est pas autorisé.', 'mime');
            return false;
         }
      }

      if (!empty($this->allowedExtensions)) {
         $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
         if (!in_array($extension, $this->allowedExtensions)) {
            $this->addError('L\'extension du fichier n\'est pas autorisée.', 'extension');
            return false;
         }
      }

      return true;
   }
}
