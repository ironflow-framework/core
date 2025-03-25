<?php

namespace IronFlow\Console\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MakeTestCommand extends Command
{
   protected static $defaultName = 'make:test';
   protected static $defaultDescription = 'Crée un nouveau test';

   protected function configure(): void
   {
      $this
         ->addArgument('name', InputArgument::REQUIRED, 'Le nom du test')
         ->addArgument('type', InputArgument::OPTIONAL, 'Le type de test (Unit|Feature)', 'Unit')
         ->addArgument('class', InputArgument::OPTIONAL, 'La classe à tester');
   }

   protected function execute(InputInterface $input, OutputInterface $output): int
   {
      $io = new SymfonyStyle($input, $output);
      $name = $input->getArgument('name');
      $type = strtolower($input->getArgument('type'));
      $class = $input->getArgument('class');

      $testContent = $this->generateTestContent($name, $type, $class);
      $testPath = "tests/{$type}/{$name}.php";

      if (!is_dir(dirname($testPath))) {
         mkdir(dirname($testPath), 0755, true);
      }

      file_put_contents($testPath, $testContent);
      $io->success("Le test {$name} a été créé avec succès !");

      return Command::SUCCESS;
   }

   protected function generateTestContent(string $name, string $type, ?string $class): string
   {
      $classUse = $class ? "use {$class};\n" : '';
      $classTest = $class ? "use {$class};\n" : '';

      return <<<PHP
<?php

namespace Tests\\{$type};

use PHPUnit\Framework\TestCase;
use IronFlow\Testing\\{$type}Test;
{$classUse}

class {$name} extends {$type}Test
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_example(): void
    {
        \$this->assertTrue(true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
PHP;
   }
}
