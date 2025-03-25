<?php

declare(strict_types=1);

namespace IronFlow\View\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ViteExtension extends AbstractExtension
{
   private static ?array $manifest = null;
   private static string $buildPath = '/assets/';
   private static bool $isDev = false;
   private static bool $devServerRunning = false;

   public function __construct()
   {
      self::$isDev = $this->isDevelopment();
      if (self::$isDev) {
         self::$devServerRunning = $this->isDevServerRunning();
      }
   }

   public function getFunctions(): array
   {
      return [
         new TwigFunction('vite_assets', [$this, 'viteAssets'], ['is_safe' => ['html']]),
         new TwigFunction('image', [$this, 'viteImage'], ['is_safe' => ['html']]),
         new TwigFunction('icon', [$this, 'viteIcon'], ['is_safe' => ['html']]),
         new TwigFunction('video', [$this, 'viteVideo'], ['is_safe' => ['html']]),
         new TwigFunction('audio', [$this, 'viteAudio'], ['is_safe' => ['html']])
      ];
   }

   private function isDevelopment(): bool
   {
      return isset($_ENV['APP_ENV']) && in_array($_ENV['APP_ENV'], ['dev', 'development', 'local']);
   }

   private function isDevServerRunning(): bool
   {
      $handle = curl_init('http://localhost:5173');
      curl_setopt_array($handle, [
         CURLOPT_RETURNTRANSFER => true,
         CURLOPT_NOBODY => true,
         CURLOPT_TIMEOUT => 1
      ]);
      $result = curl_exec($handle);
      $error = curl_errno($handle);
      curl_close($handle);
      return !$error;
   }

   private function getManifest(): array
   {
      if (self::$manifest === null) {
         $manifestPath = public_path() . self::$buildPath . 'manifest.json';
         if (file_exists($manifestPath)) {
            self::$manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
         } else {
            self::$manifest = [];
         }
      }
      return self::$manifest;
   }

   private function getAssetUrl(string $entry, string $type = 'js'): string
   {
      if (self::$isDev && self::$devServerRunning) {
         return "http://localhost:5173/assets/resources/{$type}/{$entry}";
      }

      $manifest = $this->getManifest();
      $key = $type === 'js' ? "{$entry}.js" : "{$entry}.css";
      return isset($manifest[$key]['file']) ? "/assets/" . $manifest[$key]['file'] : '';
   }

   public function viteAssets(string $entry): string
   {
      if (self::$isDev && self::$devServerRunning) {
         return sprintf(
            '<script type="module" src="http://localhost:5173/assets/@vite/client"></script>
                <script type="module" src="http://localhost:5173/assets/resources/js/%s"></script>',
            $entry
         );
      }

      $manifest = $this->getManifest();
      $html = '';

      // CSS files
      if (isset($manifest["{$entry}.css"])) {
         foreach ($manifest["{$entry}.css"]['css'] as $cssFile) {
            $html .= sprintf('<link rel="stylesheet" href="/assets/%s">', $cssFile);
         }
      }

      // JS file
      if (isset($manifest["{$entry}.js"])) {
         $html .= sprintf(
            '<script type="module" src="/assets/%s"></script>',
            $manifest["{$entry}.js"]['file']
         );
      }

      return $html;
   }

   public function viteImage(string $image, ?string $class = null, ?string $id = null, ?string $alt = null): string
   {
      $url = $this->getAssetUrl($image, 'images');
      $attrs = [];

      if ($class) $attrs[] = "class=\"{$class}\"";
      if ($id) $attrs[] = "id=\"{$id}\"";
      if ($alt) $attrs[] = "alt=\"{$alt}\"";

      return sprintf(
         '<img src="%s" %s>',
         $url,
         implode(' ', $attrs)
      );
   }

   public function viteIcon(string $icon, ?string $class = null, ?string $id = null): string
   {
      return $this->viteImage("icons/{$icon}", $class, $id, "Icon {$icon}");
   }

   public function viteVideo(string $video, ?string $class = null, ?string $id = null): string
   {
      $url = $this->getAssetUrl($video, 'videos');
      $attrs = [];

      if ($class) $attrs[] = "class=\"{$class}\"";
      if ($id) $attrs[] = "id=\"{$id}\"";

      return sprintf(
         '<video src="%s" %s controls></video>',
         $url,
         implode(' ', $attrs)
      );
   }

   public function viteAudio(string $audio, ?string $class = null, ?string $id = null): string
   {
      $url = $this->getAssetUrl($audio, 'audios');
      $attrs = [];

      if ($class) $attrs[] = "class=\"{$class}\"";
      if ($id) $attrs[] = "id=\"{$id}\"";

      return sprintf(
         '<audio src="%s" %s controls></audio>',
         $url,
         implode(' ', $attrs)
      );
   }

   public function getName(): string
   {
      return 'vite_extension';
   }
}
