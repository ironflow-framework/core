<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands;

use IronFlow\Console\Command;
use IronFlow\CraftPanel\Models\AdminUser;
use IronFlow\CraftPanel\Models\AdminRole;

class CraftPanelMakeAdminCommand extends Command
{
    protected string $signature = 'craft:panel:make-admin';
    protected string $description = 'Create a new administrator for the CraftPanel';

    public function handle(): int
    {
        $name = $this->ask('Enter administrator name:');
        $email = $this->ask('Enter administrator email:');
        $password = $this->secret('Enter administrator password:');
        $confirmPassword = $this->secret('Confirm administrator password:');

        if ($password !== $confirmPassword) {
            $this->error('Passwords do not match.');
            return Command::FAILURE;
        }

        // Vérifier si l'email existe déjà
        if (AdminUser::where('email', $email)->exists()) {
            $this->error('An administrator with this email already exists.');
            return Command::FAILURE;
        }

        // Créer l'administrateur
        $admin = AdminUser::create([
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        // Vérifier si c'est le premier administrateur
        if (AdminUser::count() === 1) {
            // Créer le rôle super admin s'il n'existe pas
            $superAdminRole = AdminRole::firstOrCreate(
                ['name' => 'super-admin'],
                ['description' => 'Super Administrator with full access']
            );

            // Assigner le rôle super admin
            $admin->roles()->attach($superAdminRole->id);

            $this->info('Created first administrator with super-admin role.');
        } else {
            // Demander quels rôles assigner
            $roles = AdminRole::all();
            $selectedRoles = $this->choice(
                'Select roles to assign (comma-separated numbers):',
                $roles->pluck('name')->toArray(),
                null,
                null,
                true
            );

            $roleIds = AdminRole::whereIn('name', $selectedRoles)->pluck('id');
            $admin->roles()->attach($roleIds);
        }

        $this->info("Administrator {$name} created successfully!");
        return Command::SUCCESS;
    }
}
