<?php

namespace IronFlow\Tests\Installer;

use IronFlow\Installer\Installer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class InstallerTest extends TestCase
{
   private $input;
   private $output;
   private $io;

   protected function setUp(): void
   {
      $this->input = new ArrayInput([]);
      $this->output = new BufferedOutput();
      $this->io = new SymfonyStyle($this->input, $this->output);
   }

   /**
    * Test de la vérification des prérequis
    */
   public function testCheckRequirements(): void
   {
      // Test avec PHP 8.2 (devrait passer)
      $this->assertTrue(version_compare(PHP_VERSION, '8.2.0', '>='));

      // Test des extensions requises
      $requiredExtensions = ['pdo', 'mbstring', 'xml', 'curl', 'json'];
      foreach ($requiredExtensions as $ext) {
         $this->assertTrue(extension_loaded($ext), "L'extension {$ext} n'est pas chargée");
      }
   }

   /**
    * Test de la configuration de l'environnement
    */
   public function testSetupEnvironment(): void
   {
      // Créer un fichier .env.example temporaire
      $envExample = "APP_NAME=IronFlow\nAPP_ENV=local\nAPP_KEY=\n";
      file_put_contents('.env.example', $envExample);

      // Supprimer le fichier .env s'il existe
      if (file_exists('.env')) {
         unlink('.env');
      }

      // Exécuter la configuration
      Installer::setupEnvironment($this->io);

      // Vérifier que le fichier .env a été créé
      $this->assertFileExists('.env');

      // Nettoyer
      unlink('.env');
      unlink('.env.example');
   }

   /**
    * Test de l'installation des dépendances
    */
   public function testInstallDependencies(): void
   {
      // Créer un package.json temporaire
      $packageJson = json_encode([
         'name' => 'test/package',
         'version' => '1.0.0',
         'dependencies' => []
      ]);
      file_put_contents('package.json', $packageJson);

      // Exécuter l'installation
      Installer::installDependencies($this->io);

      // Vérifier que node_modules existe (si npm est installé)
      if (shell_exec('which npm')) {
         $this->assertDirectoryExists('node_modules');
      }

      // Nettoyer
      unlink('package.json');
      if (is_dir('node_modules')) {
         $this->removeDirectory('node_modules');
      }
   }

   /**
    * Test de la mise à jour des dépendances
    */
   public function testUpdateDependencies(): void
   {
      // Créer un package.json temporaire
      $packageJson = json_encode([
         'name' => 'test/package',
         'version' => '1.0.0',
         'dependencies' => []
      ]);
      file_put_contents('package.json', $packageJson);

      // Exécuter la mise à jour
      Installer::updateDependencies($this->io);

      // Nettoyer
      unlink('package.json');
   }

   /**
    * Supprime récursivement un répertoire
    */
   private function removeDirectory(string $dir): void
   {
      if (is_dir($dir)) {
         $objects = scandir($dir);
         foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
               if (is_dir($dir . "/" . $object)) {
                  $this->removeDirectory($dir . "/" . $object);
               } else {
                  unlink($dir . "/" . $object);
               }
            }
         }
         rmdir($dir);
      }
   }
}
