<?php

declare(strict_types=1);

namespace PreemStudio\Messageable;

use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-messageable')
            ->hasMigration('create_participants_table')
            ->hasMigration('create_messages_table')
            ->hasMigration('create_threads_table')
            ->hasInstallCommand(fn (InstallCommand $command) => $command->publishMigrations());
    }
}
