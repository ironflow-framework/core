<?php

declare(strict_types=1);

namespace IronFlow\Vibe\Components;

use IronFlow\View\Component;

class FileUploader extends Component
{
   /**
    * Identifiant du champ de formulaire
    *
    * @var string
    */
   protected string $id;

   /**
    * Nom du champ de formulaire
    *
    * @var string
    */
   protected string $name;

   /**
    * Label du champ de formulaire
    *
    * @var string
    */
   protected string $label;

   /**
    * Accepter plusieurs fichiers
    *
    * @var bool
    */
   protected bool $multiple;

   /**
    * Types de fichiers acceptés
    *
    * @var string|null
    */
   protected ?string $accept;

   /**
    * Classes CSS additionnelles
    *
    * @var string
    */
   protected string $class;

   /**
    * Options avancées pour Dropzone.js
    *
    * @var array
    */
   protected array $options;

   /**
    * URL d'upload
    *
    * @var string
    */
   protected string $url;

   /**
    * Crée une instance du composant
    *
    * @param string $name Nom du champ de formulaire
    * @param string $url URL d'upload
    * @param string $label Label du champ
    * @param bool $multiple Accepter plusieurs fichiers
    * @param string|null $accept Types de fichiers acceptés
    * @param string $class Classes CSS additionnelles
    * @param array $options Options avancées pour Dropzone.js
    */
   public function __construct(
      string $name,
      string $url,
      string $label = 'Téléverser des fichiers',
      bool $multiple = false,
      ?string $accept = null,
      string $class = '',
      array $options = []
   ) {
      $this->id = 'file-upload-' . uniqid();
      $this->name = $name;
      $this->url = $url;
      $this->label = $label;
      $this->multiple = $multiple;
      $this->accept = $accept;
      $this->class = $class;
      $this->options = $options;
   }

   /**
    * Génère le rendu du composant
    *
    * @return string
    */
   public function render(): string
   {
      $multipleAttr = $this->multiple ? 'multiple' : '';
      $acceptAttr = $this->accept ? "accept=\"{$this->accept}\"" : '';
      $baseClass = 'file-uploader';
      $fullClass = trim("{$baseClass} {$this->class}");

      // Rendu de base sans JavaScript
      $basicUploader = <<<HTML
        <div class="{$fullClass}" id="{$this->id}-container">
            <div class="file-uploader-label">
                <label for="{$this->id}">{$this->label}</label>
            </div>
            <div class="file-uploader-input">
                <input 
                    type="file" 
                    id="{$this->id}" 
                    name="{$this->name}" 
                    {$multipleAttr} 
                    {$acceptAttr}
                    class="file-input"
                >
            </div>
            <div class="file-uploader-preview" id="{$this->id}-preview"></div>
        </div>
        HTML;

      // Utilisation de Dropzone.js pour une expérience améliorée
      if ($this->shouldUseDropzone()) {
         return $this->renderDropzone();
      }

      return $basicUploader;
   }

   /**
    * Détermine si Dropzone.js doit être utilisé
    *
    * @return bool
    */
   protected function shouldUseDropzone(): bool
   {
      return isset($this->options['useDropzone']) && $this->options['useDropzone'] === true;
   }

   /**
    * Génère le rendu avec Dropzone.js
    *
    * @return string
    */
   protected function renderDropzone(): string
   {
      $multipleAttr = $this->multiple ? 'true' : 'false';
      $acceptAttr = $this->accept ?: '';
      $baseClass = 'dropzone file-uploader';
      $fullClass = trim("{$baseClass} {$this->class}");

      $options = array_merge([
         'url' => $this->url,
         'paramName' => $this->name,
         'maxFiles' => $this->multiple ? null : 1,
         'addRemoveLinks' => true,
         'acceptedFiles' => $acceptAttr,
         'dictDefaultMessage' => "Déposez les fichiers ici ou cliquez pour parcourir",
         'dictFallbackMessage' => "Votre navigateur ne supporte pas le glisser-déposer.",
         'dictFallbackText' => "Utilisez le formulaire ci-dessous pour téléverser vos fichiers.",
         'dictFileTooBig' => "Le fichier est trop volumineux ({{filesize}}MB). Taille maximale: {{maxFilesize}}MB.",
         'dictInvalidFileType' => "Ce type de fichier n'est pas autorisé.",
         'dictResponseError' => "Erreur du serveur (code {{statusCode}}).",
         'dictCancelUpload' => "Annuler",
         'dictUploadCanceled' => "Téléversement annulé.",
         'dictCancelUploadConfirmation' => "Êtes-vous sûr de vouloir annuler ce téléversement?",
         'dictRemoveFile' => "Supprimer",
         'dictMaxFilesExceeded' => "Vous ne pouvez pas téléverser plus de fichiers.",
      ], $this->options);

      $optionsJson = json_encode($options);

      return <<<HTML
        <div class="{$fullClass}" id="{$this->id}-dropzone">
            <div class="dz-message">
                <div class="dz-message-text">
                    <p>{$options['dictDefaultMessage']}</p>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Vérifier si Dropzone.js est chargé
                if (typeof Dropzone !== 'undefined') {
                    Dropzone.autoDiscover = false;
                    
                    var options = {$optionsJson};
                    var dropzone = new Dropzone("#{$this->id}-dropzone", options);
                    
                    // Événements Dropzone
                    dropzone.on('success', function(file, response) {
                        if (response && response.id) {
                            // Ajouter l'ID du fichier téléversé à un champ caché
                            var input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = '{$this->name}' + (options.maxFiles != 1 ? '[]' : '');
                            input.value = response.id;
                            document.getElementById('{$this->id}-dropzone').appendChild(input);
                        }
                    });
                } else {
                    console.error('Dropzone.js is not loaded. Please include the library.');
                }
            });
        </script>
        HTML;
   }
}
