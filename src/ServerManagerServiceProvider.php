<?php

namespace Metacomet\ServerManager;

use Illuminate\Support\Facades\Gate;
use Metacomet\ServerManager\Commands\CleanupSessionsCommand;
use Metacomet\ServerManager\Commands\InstallCommand;
use Metacomet\ServerManager\Commands\ServerManagerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServerManagerServiceProvider extends PackageServiceProvider
{
    public function packageRegistered(): void
    {
        $this->app->singleton(\Metacomet\ServerManager\Services\SessionManager::class);
        $this->app->singleton(\Metacomet\ServerManager\Services\TerminalService::class);
    }

    public function packageBooted(): void
    {
        // Register policies
        Gate::policy(\Metacomet\ServerManager\Models\Server::class, \Metacomet\ServerManager\Policies\ServerPolicy::class);
        Gate::policy(\Metacomet\ServerManager\Models\Session::class, \Metacomet\ServerManager\Policies\SessionPolicy::class);

        // Register the local server access gate
        Gate::define(is_string(config('server-manager.local_server_gate', 'server-manager:access-local')) ? config('server-manager.local_server_gate', 'server-manager:access-local') : 'server-manager:access-local', function (mixed $user): bool {
            // By default, only allow if user has a specific permission
            // This can be customized in the app
            return is_object($user) && method_exists($user, 'can') ? $user->can('access-local-server') : false;
        });

        // Register broadcasting channels
        $this->registerBroadcastingChannels();
    }

    protected function registerBroadcastingChannels(): void
    {
        require __DIR__.'/../routes/channels.php';
    }

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('server-manager')
            ->hasConfigFile()
            ->hasMigration('create_server_manager_tables')
            ->hasCommands([
                ServerManagerCommand::class,
                CleanupSessionsCommand::class,
                InstallCommand::class,
            ])
            ->hasRoute('api');

        // Register web routes if enabled
        if (config('server-manager.web.enabled', true)) {
            $package->hasRoute('web');

            // Register views
            $package->hasViews('server-manager');

            // Register assets for publishing
            $package->hasAssets();
        }
    }

    public function boot(): void
    {
        parent::boot();

        // Publish frontend assets
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../dist' => public_path('vendor/server-manager'),
            ], 'server-manager-assets');

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/server-manager'),
                __DIR__.'/../resources/css' => resource_path('css/vendor/server-manager'),
            ], 'server-manager-frontend');

            $this->publishes([
                __DIR__.'/../package.json' => base_path('package-server-manager.json'),
                __DIR__.'/../vite.config.mts' => base_path('vite.config.server-manager.mts'),
                __DIR__.'/../tailwind.config.js' => base_path('tailwind.config.server-manager.js'),
            ], 'server-manager-build');
        }
    }
}
