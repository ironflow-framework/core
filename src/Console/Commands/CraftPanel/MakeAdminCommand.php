<?php

namespace IronFlow\Console\Commands\CraftPanel;

use IronFlow\Support\Facades\Config;
use IronFlow\Support\Facades\Validator;
use IronFlow\Support\Security\Hasher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;

class MakeAdminCommand extends Command
{
    protected static $defaultName = 'craft:make-admin';
    protected static $defaultDescription = 'Crée un nouvel administrateur pour le CraftPanel';

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email de l\'administrateur')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe de l\'administrateur')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Nom de l\'administrateur');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Assistant de création d\'administrateur CraftPanel');

        try {
            // Récupération des informations
            $email = $input->getOption('email') ?? $this->askForEmail($io);
            $password = $input->getOption('password') ?? $this->askForPassword($io);
            $name = $input->getOption('name') ?? $this->askForName($io);

            // Validation
            $validator = Validator::make([
                'email' => $email,
                'password' => $password,
                'name' => $name
            ], [
                'email' => 'required|email',
                'password' => 'required|min:8',
                'name' => 'required|string|max:255'
            ]);

            if (!$validator->passes()) {
                foreach ($validator->errors() as $error) {
                    $io->error($error);
                }
                return Command::FAILURE;
            }

            // Création de l'administrateur
            $this->createAdmin($email, $password, $name);

            $io->success('Administrateur créé avec succès !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de la création de l\'administrateur : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function askForEmail(SymfonyStyle $io): string
    {
        return $io->ask('Email de l\'administrateur', null, function ($email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Email invalide');
            }
            return $email;
        });
    }

    protected function askForPassword(SymfonyStyle $io): string
    {
        $question = new Question('Mot de passe de l\'administrateur');
        $question->setHidden(true);
        $question->setHiddenFallback(false);

        return $io->askQuestion($question);
    }

    protected function askForName(SymfonyStyle $io): string
    {
        return $io->ask('Nom de l\'administrateur');
    }

    protected function createAdmin(string $email, string $password, string $name): void
    {
        $userClass = Config::get('auth.providers.users.model');
        $user = new $userClass();
        
        $user->email = $email;
        $user->password = Hasher::hash($password);
        $user->name = $name;
        $user->is_admin = true;
        
        $user->save();
    }
}
