<?php

namespace Yuisa\VoltiaDatatable;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Yuisa\VoltiaDatatable\Commands\VoltiaDatatableCommand;

class VoltiaDatatableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('voltia-datatable')
            ->hasConfigFile()
            ->hasCommand(VoltiaDatatableCommand::class);
    }
}
