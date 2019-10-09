<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AzureStorageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Storage::extend('azure', function($app, $config) {
            $endpoint = sprintf(
                'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
                $config['name'],
                $config['key']
            );
            $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($endpoint);
            return new Filesystem(new AzureAdapter($blobRestProxy, $config['container']));
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
