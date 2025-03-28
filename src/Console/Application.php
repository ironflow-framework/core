<?php 

namespace IronFlow\Console;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Command\Command;
class Application extends ConsoleApplication
{
   public function __construct()
   {
      parent::__construct('IronFlow', '1.0.0');
   }

   public function add(Command $command): void
   {
      $this->add($command);
   }
}
