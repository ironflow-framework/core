<?php

namespace IronFlow\Console\Commands\CraftPanel;

use Illuminate\Support\Str;
use IronFlow\Console\Commands\Command;
use IronFlow\Support\Facades\Auth;
use IronFlow\Support\Facades\Config;
use IronFlow\Support\Hasher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeAdminCommand extends Command
{
    protected $signature = 'craftpanel:make-admin {email : Adresse email de l\'administrateur} {password? : Mot de passe de l\'administrateur}';

    protected $description = 'Créer un administrateur pour le CraftPanel';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password') ?: $this->generatePassword();

        $this->info("Création de l'administrateur...");

        // Créer l'utilisateur
        $user = $this->createAdmin($email, $password);

        // Affecter les permissions
        $this->assignAdminPermissions($user);

        $this->info("L'administrateur a été créé avec succès !");
        $this->info("Email: {$email}");
        $this->info("Mot de passe: {$password}");

        return 0;
    }

    private function generatePassword(): string
    {
        return Str::random(16);
    }

    private function createAdmin(string $email, string $password)
    {
        $userClass = Config::get('auth.providers.users.model');
        
        return $userClass::create([
            'name' => 'Administrateur',
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    }

    private function assignAdminPermissions($user): void
    {
        // Récupérer toutes les permissions du CraftPanel
        $permissions = Config::get('craftpanel.permissions', []);
        
        // Affecter toutes les permissions
        foreach ($permissions as $permission) {
            $user->assignPermission($permission);
        }
    }
}
