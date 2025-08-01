<?php

namespace Metacomet\ServerManager\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    public $signature = 'server-manager:install {--with-frontend : Install frontend assets} {--force : Overwrite existing files}';

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

        // Install frontend assets
        if ($this->option('with-frontend')) {
            $this->installFrontend();
        } elseif ($this->confirm('Do you want to install the frontend assets?', true)) {
            $this->installFrontend();
        }

        // Configure broadcasting
        if ($this->confirm('Do you want to configure Laravel Reverb for WebSocket support?', true)) {
            $this->configureBroadcasting();
        }

        $this->info('Server Manager installed successfully!');
        $this->line('');
        $this->info('Next steps:');
        $this->line('- Configure your settings in config/server-manager.php');
        $this->line('- Set up the access gate for local server access');
        if ($this->option('with-frontend')) {
            $this->line('- Run "npm install && npm run build" to build frontend assets');
        }
        $this->line('- Visit '.config('app.url').'/'.config('server-manager.web.prefix', 'server-manager'));

        return self::SUCCESS;
    }

    protected function installFrontend(): void
    {
        $this->info('Publishing frontend assets...');

        // Publish frontend source
        $this->call('vendor:publish', [
            '--provider' => 'Metacomet\ServerManager\ServerManagerServiceProvider',
            '--tag' => 'server-manager-frontend',
            '--force' => $this->option('force'),
        ]);

        // Publish build files
        $this->call('vendor:publish', [
            '--provider' => 'Metacomet\ServerManager\ServerManagerServiceProvider',
            '--tag' => 'server-manager-build',
            '--force' => $this->option('force'),
        ]);

        // Add NPM dependencies
        $this->info('Adding NPM dependencies...');
        $this->updateNodePackages(function (array $packages): array {
            return [
                '@inertiajs/react' => '^1.0.0',
                'react' => '^18.2.0',
                'react-dom' => '^18.2.0',
                'laravel-echo' => '^1.16.0',
                'pusher-js' => '^8.3.0',
                'xterm' => '^5.3.0',
                'xterm-addon-fit' => '^0.8.0',
                'xterm-addon-web-links' => '^0.9.0',
            ] + $packages;
        });

        $this->updateNodePackages(function (array $packages): array {
            return [
                '@vitejs/plugin-react' => '^4.0.0',
                '@tailwindcss/forms' => '^0.5.9',
                '@tailwindcss/vite' => '^4.1.11',
                'tailwindcss' => '^4.1.11',
                'typescript' => '^5.7.0',
                '@types/react' => '^19.0.0',
                '@types/react-dom' => '^19.0.0',
            ] + $packages;
        }, true);
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

    protected function updateNodePackages(callable $callback, bool $dev = false): void
    {
        if (! file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT).PHP_EOL
        );
    }
}
