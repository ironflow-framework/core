<?php

declare(strict_types=1);

namespace IronFlow\Console\Commands;


use IronFlow\Database\Migrations\Migration;

class AuthInstallCommand
{
    protected string $signature = 'auth:install';
    protected string $description = 'Install authentication system';

    public function handle(): void
    {
        $this->info('Installing authentication system...');

        // Create migrations
        $this->createMigrations();

        // Copy views
        $this->copyViews();

        // Copy controllers
        $this->copyControllers();

        // Add routes
        $this->addRoutes();

        $this->info('Authentication system installed successfully.');
    }

    protected function createMigrations(): void
    {
        $migration = new Migration();
        
        // Create users table
        $migration->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });

        // Create password resets table
        $migration->create('password_resets', function ($table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    protected function copyViews(): void
    {
        $views = [
            'auth/login.php',
            'auth/register.php',
            'auth/forgot-password.php',
            'auth/reset-password.php',
            'auth/verify-email.php',
        ];

        foreach ($views as $view) {
            $this->copyStub(
                "stubs/auth/views/{$view}",
                "resources/views/{$view}"
            );
        }
    }

    protected function copyControllers(): void
    {
        $controllers = [
            'Auth/LoginController.php',
            'Auth/RegisterController.php',
            'Auth/ForgotPasswordController.php',
            'Auth/ResetPasswordController.php',
            'Auth/VerificationController.php',
        ];

        foreach ($controllers as $controller) {
            $this->copyStub(
                "stubs/auth/controllers/{$controller}",
                "app/Controllers/{$controller}"
            );
        }
    }

    protected function addRoutes(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $authRoutes = file_get_contents(__DIR__ . '/stubs/auth/routes.stub');

        file_put_contents(
            base_path('routes/web.php'),
            $routes . "\n" . $authRoutes
        );
    }

    protected function copyStub(string $stub, string $target): void
    {
        if (!file_exists(dirname($target))) {
            mkdir(dirname($target), 0755, true);
        }

        copy(
            __DIR__ . '/' . $stub,
            base_path($target)
        );
    }
}
