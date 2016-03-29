<?php namespace NetForceWS\IO;

use \Storage;

class IoServiceProvider extends \NetForceWS\Support\ServiceProvider
{
    /**
     * Lista de provider para trocar instancias.
     *
     * @var array
     */
    protected $instances = [
        'files' => '\NetForceWS\IO\Filesystem',
    ];

    /**
     * Boot do Provider.
     */
    public function boot()
    {
        parent::boot();

        // Storage NetForceWS
        Storage::extend('ntws', function ($app, $config) {
            return new \NetForceWS\IO\Storage($app, $config);
        });
    }
}