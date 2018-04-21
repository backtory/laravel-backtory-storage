<?php

namespace Backtory\Storage\Laravel;

use Backtory\Storage\Core\Contract\Keys;
use Backtory\Storage\Core\Facade\BacktoryStorage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Storage;

/**
 * Class BacktoryStorageServiceProvider
 * @package Backtory\Storage\Laravel
 */
class BacktoryStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('backtory', function ($app, $config) {
            BacktoryStorage::init(
                $config[Keys::X_BACKTORY_AUTHENTICATION_ID],
                $config[Keys::X_BACKTORY_AUTHENTICATION_KEY],
                $config[Keys::X_BACKTORY_STORAGE_ID]
            );

            if (isset($config[Keys::DOMAIN])) {
                BacktoryStorage::setDomain($config[Keys::DOMAIN]);
            }

            if (isset($config[Keys::HEADERS])) {
                BacktoryStorage::setHeader($config[Keys::HEADERS]);
            }
            if (isset($config[Keys::PARAMETERS])) {
                BacktoryStorage::setParameters($config[Keys::PARAMETERS]);
            }

            return new Filesystem(
                new BacktoryStorageDriverAdapter(isset($config["pathPrefix"]) ? $config["pathPrefix"] : "")
            );
        });

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
