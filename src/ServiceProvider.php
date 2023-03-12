<?php

declare(strict_types=1);

namespace PreemStudio\Messageable;

use PreemStudio\Jetpack\Package\AbstractServiceProvider;
use PreemStudio\Jetpack\Package\Package;

class ServiceProvider extends AbstractServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-messageable')
            ->hasMigration('create_participants_table')
            ->hasMigration('create_messages_table')
            ->hasMigration('create_threads_table');
    }
}
