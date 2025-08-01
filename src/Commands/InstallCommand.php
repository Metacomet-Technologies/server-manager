<?php

namespace Metacomet\ServerManager\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    public $signature = 'server-manager:install {--force : Overwrite existing files}';

    public $description = 'Install the Server Manager package';

    public function handle(): int
    {
        $this->info('Installing Server Manager...');

        // Publish config
        $this->info('Publishing configuration...');
        $this->call('vendor:publish', [
            '--provider' => 'Metacomet\ServerManager\ServerManagerServiceProvider',
            '--tag' => 'server-manager-config',
            '--force' => $this->option('force'),
        ]);

        // Publish migrations
        $this->info('Publishing migrations...');
        $this->call('vendor:publish', [
            '--provider' => 'Metacomet\ServerManager\ServerManagerServiceProvider',
            '--tag' => 'server-manager-migrations',
            '--force' => $this->option('force'),
        ]);

        // Run migrations
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
        }

        // Frontend is now self-contained, no installation needed
        $this->info('✓ Frontend assets are served directly from the package');

        // Configure broadcasting
        if ($this->confirm('Do you want to configure Laravel Reverb for WebSocket support?', true)) {
            $this->configureBroadcasting();
        }

        $this->info('Server Manager installed successfully!');
        $this->line('');
        $this->info('Next steps:');
        $this->line('- Configure your settings in config/server-manager.php');
        $this->line('- Set up the access gate for local server access');
        $this->line('- Visit '.config('app.url').'/'.config('server-manager.web.prefix', 'server-manager'));

        return self::SUCCESS;
    }


    protected function configureBroadcasting(): void
    {
        $this->info('Configuring Laravel Reverb...');

        // Check if Reverb is installed
        if (! class_exists(\Laravel\Reverb\ReverbServiceProvider::class)) {
            $this->warn('Laravel Reverb is not installed. Installing...');
            $this->line('composer require laravel/reverb');
            $this->warn('Please run the above command and then run this installer again.');

            return;
        }

        // Install Reverb
        $this->call('reverb:install');

        $this->info('Reverb configured successfully!');
        $this->line('Remember to start Reverb with: php artisan reverb:start');
    }

}
