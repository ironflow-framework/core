<?php

namespace IronFlow\Console\Commands\Generator;

use IronFlow\Support\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use IronFlow\Support\Hasher;
use IronFlow\Validation\Validator;

class MakeAdminCommand extends Command
{
    protected static $defaultName = 'craftpanel:make-admin';
    protected static $defaultDescription = 'Assistant de création d\'un administrateur pour le CraftPanel';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Assistant de création d\'administrateur CraftPanel');

        // Validation d'email
        $validator = new Validator();
        $validator->addRule('email', 'required|email');
        $validator->addRule('password', 'required|min:8');
        $validator->addRule('name', 'string|max:255');

        do {
            $email = $io->ask('Entrez l\'adresse email de l\'administrateur', null, function ($email) use ($validator, $io) {
                if (!$validator->validate('email', $email)) {
                    $io->error($validator->errors());
                    return null;
                }
                return $email;
            });
        } while (!$validator->validate('email', $email));

        do {
            $password = $io->askHidden('Entrez le mot de passe', function ($password) use ($validator, $io) {
                if (!$validator->validate('password', $password)) {
                    $io->error($validator->errors());
                    return null;
                }
                return $password;
            });
        } while (!$validator->validate('password', $password));

        do {
            $name = $io->ask("Nom de l\'administrateur", null, function ($name) use ($validator, $io) {
                if (!$validator->validate('name', $name)) {
                    $io->error($validator->errors());
                    return null;
                }
                return $name;
            });
        } while (!$validator->validate('name', $name));

        // Confirmation
        $io->section('Récapitulatif');
        $confirm = $io->confirm(sprintf(
            "Voulez-vous créer un administrateur avec les détails suivants ?\n- Email: %s\n- Nom: %s",
            $email,
            $name
        ), false);

        if (!$confirm) {
            $io->warning('Création de l\'administrateur annulée.');
            return Command::FAILURE;
        }

        try {
            $user = $this->createAdmin($email, $password, $name);
            $this->assignAdminPermissions($user);

            $io->success('Administrateur créé avec succès !');

            $io->table(
                ['Champ', 'Valeur'],
                [
                    ['Email', $email],
                    ['Nom', $name],
                    ['Mot de passe', $password]
                ]
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Erreur lors de la création : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }


    private function createAdmin(string $email, string $password, string $name)
    {
        $userClass = Config::get('auth.providers.users.model');

        return $userClass::create([
            'name' => $name,
            'email' => $email,
            'password' => Hasher::hash($password),
            'role' => 'admin'
        ]);
    }

    private function assignAdminPermissions($user): void
    {
        $permissions = Config::get('craftpanel.permissions', [
            'view_dashboard',
            'manage_users',
            'manage_settings',
            'crud_all_models'
        ]);

        foreach ($permissions as $permission) {
            $user->assignPermission($permission);
        }
    }
}
