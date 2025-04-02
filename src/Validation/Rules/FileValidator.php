<?php

declare(strict_types=1);

namespace IronFlow\Validation\Rules;

use IronFlow\Validation\AbstractRule;

class FileValidator extends AbstractRule
{

   /**
    * Types MIME autorisés
    */
   private const ALLOWED_MIME_TYPES = [
      'image/jpeg',
      'image/png',
      'image/gif',
      'image/webp',
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'text/plain',
   ];

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

   public function validate(string $field, mixed $value, array $parameters = [], array $data = []): bool
   {
      if (empty($value)) {
         return !$this->required;
      }

      if (!is_array($value)) {
         $value = [$value];
      }

      foreach ($value as $file) {
         if (!$this->validateFile($file, $field)) {
            return false;
         }
      }

      return true;
   }

   protected function validateFile($file, string $field): bool
   {
      if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
         $this->setAttribute('field', $field);
         return false;
      }

      if ($this->maxSize > 0 && $file['size'] > $this->maxSize) {
         $this->setAttribute('field', $field);
         return false;
      }

      if (!empty($this->allowedMimeTypes)) {
         $finfo = finfo_open(FILEINFO_MIME_TYPE);
         $mimeType = finfo_file($finfo, $file['tmp_name']);
         finfo_close($finfo);

         if (!in_array($mimeType, $this->allowedMimeTypes)) {
            $this->setAttribute('field', $field);
            return false;
         }
      }

      if (!empty($this->allowedExtensions)) {
         $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
         if (!in_array($extension, $this->allowedExtensions)) {
            $this->setAttribute('field', $field);
            return false;
         }
      }

      return true;
   }
}
