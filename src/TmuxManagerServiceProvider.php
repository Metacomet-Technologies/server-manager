<?php

namespace Metacomet\TmuxManager;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Metacomet\TmuxManager\Commands\TmuxManagerCommand;

class TmuxManagerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('tmux-manager')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_tmux_manager_table')
            ->hasCommand(TmuxManagerCommand::class);
    }
}
