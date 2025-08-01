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

        // Set custom Inertia root view for server-manager routes
        if (config('server-manager.web.enabled', true)) {
            $this->loadViewsFrom(__DIR__.'/../resources/views', 'server-manager');

            // Configure Inertia to use our custom layout for server-manager routes
            if ($this->app->has('inertia')) {
                $this->app->afterResolving('inertia', function ($inertia) {
                    $prefix = config('server-manager.web.prefix', 'server-manager');
                    if (is_string($prefix) && request()->is($prefix.'*')) {
                        $inertia->setRootView('server-manager::app');
                    }
                });
            }

            // Register asset routes
            $this->registerAssetRoutes();
        }

        // Only publish config and migrations - everything else is self-contained
        if ($this->app->runningInConsole()) {
            // For users who want to customize the views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/server-manager'),
            ], 'server-manager-views');
        }
    }

    protected function registerAssetRoutes(): void
    {
        $prefix = config('server-manager.web.prefix', 'server-manager');

        // Serve assets directly from the package
        \Route::get((string) $prefix.'/assets/{path}', function ($path) {
            $assetPath = __DIR__.'/../dist/'.$path;

            if (! file_exists($assetPath)) {
                abort(404);
            }

            $mimeTypes = [
                'css' => 'text/css',
                'js' => 'application/javascript',
                'json' => 'application/json',
                'woff' => 'font/woff',
                'woff2' => 'font/woff2',
                'ttf' => 'font/ttf',
                'eot' => 'application/vnd.ms-fontobject',
                'svg' => 'image/svg+xml',
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'gif' => 'image/gif',
                'png' => 'image/png',
                'ico' => 'image/x-icon',
            ];

            $extension = pathinfo($path, PATHINFO_EXTENSION);
            $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';

            return response()->file($assetPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        })->where('path', '.*')->name('server-manager.assets');
    }
}
