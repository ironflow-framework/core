<?php

declare(strict_types=1);

namespace IronFlow\Service\Vibe\Components;

use IronFlow\Service\Vibe\Models\Media;
use IronFlow\View\Component;

class MediaPlayer extends Component
{
   /**
    * Type de média (audio ou video)
    *
    * @var string
    */
   protected string $type;

   /**
    * URL du média
    *
    * @var string
    */
   protected string $src;

   /**
    * Largeur du lecteur (pour vidéo)
    *
    * @var string
    */
   protected string $width;

   /**
    * Hauteur du lecteur (pour vidéo)
    *
    * @var string
    */
   protected string $height;

   /**
    * Afficher les contrôles du lecteur
    *
    * @var bool
    */
   protected bool $controls;

   /**
    * Lecture automatique
    *
    * @var bool
    */
   protected bool $autoplay;

   /**
    * Lecture en boucle
    *
    * @var bool
    */
   protected bool $loop;

   /**
    * Rendre le lecteur muet
    *
    * @var bool
    */
   protected bool $muted;

   /**
    * Texte alternatif pour le média
    *
    * @var string|null
    */
   protected ?string $alt;

   /**
    * Crée une instance du composant
    *
    * @param string $src URL du média
    * @param string $type Type de média (audio ou video)
    * @param string $width Largeur du lecteur (pour vidéo)
    * @param string $height Hauteur du lecteur (pour vidéo)
    * @param bool $controls Afficher les contrôles du lecteur
    * @param bool $autoplay Lecture automatique
    * @param bool $loop Lecture en boucle
    * @param bool $muted Rendre le lecteur muet
    * @param string|null $alt Texte alternatif pour le média
    */
   public function __construct(
      string $src,
      string $type = 'video',
      string $width = '100%',
      string $height = 'auto',
      bool $controls = true,
      bool $autoplay = false,
      bool $loop = false,
      bool $muted = false,
      ?string $alt = null
   ) {
      $this->src = $src;
      $this->type = in_array($type, ['audio', 'video']) ? $type : 'video';
      $this->width = $width;
      $this->height = $height;
      $this->controls = $controls;
      $this->autoplay = $autoplay;
      $this->loop = $loop;
      $this->muted = $muted;
      $this->alt = $alt;
   }

   /**
    * Crée une instance depuis un objet Media
    *
    * @param Media $media
    * @param array $attributes Attributs additionnels
    * @return self
    */
   public static function fromMedia(Media $media, array $attributes = []): self
   {
      $type = $media->isVideo() ? 'video' : ($media->isAudio() ? 'audio' : 'video');

      return new self(
         $media->getUrl(),
         $type,
         $attributes['width'] ?? '100%',
         $attributes['height'] ?? 'auto',
         $attributes['controls'] ?? true,
         $attributes['autoplay'] ?? false,
         $attributes['loop'] ?? false,
         $attributes['muted'] ?? false,
         $media->alt ?? null
      );
   }

   /**
    * Génère le rendu du composant
    *
    * @return string
    */
   public function render(): string
   {
      if ($this->type === 'audio') {
         return $this->renderAudio();
      }

      return $this->renderVideo();
   }

   /**
    * Génère le rendu du lecteur audio
    *
    * @return string
    */
   protected function renderAudio(): string
   {
      $attrs = $this->getBaseAttributes();
      unset($attrs['width'], $attrs['height']);

      $attributesStr = $this->buildAttributesString($attrs);

      return "<audio {$attributesStr}>
            <source src=\"{$this->src}\" type=\"audio/mp3\">
            <p>Votre navigateur ne supporte pas la lecture audio HTML5.</p>
        </audio>";
   }

   /**
    * Génère le rendu du lecteur vidéo
    *
    * @return string
    */
   protected function renderVideo(): string
   {
      $attrs = $this->getBaseAttributes();

      $attributesStr = $this->buildAttributesString($attrs);

      return "<video {$attributesStr}>
            <source src=\"{$this->src}\" type=\"video/mp4\">
            <p>Votre navigateur ne supporte pas la lecture vidéo HTML5.</p>
        </video>";
   }

   /**
    * Obtient les attributs de base pour le lecteur
    *
    * @return array
    */
   protected function getBaseAttributes(): array
   {
      $attrs = [
         'src' => $this->src,
         'width' => $this->width,
         'height' => $this->height,
      ];

      if ($this->controls) {
         $attrs['controls'] = 'controls';
      }

      if ($this->autoplay) {
         $attrs['autoplay'] = 'autoplay';
      }

      if ($this->loop) {
         $attrs['loop'] = 'loop';
      }

      if ($this->muted) {
         $attrs['muted'] = 'muted';
      }

      if ($this->alt) {
         $attrs['aria-label'] = $this->alt;
      }

      return $attrs;
   }

   /**
    * Construit une chaîne d'attributs HTML
    *
    * @param array $attributes
    * @return string
    */
   protected function buildAttributesString(array $attributes): string
   {
      $attributeStrings = [];

      foreach ($attributes as $key => $value) {
         if ($value === true || $value === $key) {
            $attributeStrings[] = $key;
         } elseif ($value !== false && $value !== null) {
            $attributeStrings[] = $key . '="' . htmlspecialchars($value) . '"';
         }
      }

      return implode(' ', $attributeStrings);
   }
}
