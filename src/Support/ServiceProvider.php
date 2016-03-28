<?php namespace NetForceWS\Support;

use Illuminate\Support\Facades\Facade;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Lista de comandos para ser registrado
     * @var array
     */
    protected $commands = [];

    /**
     * Lista de providers
     * @var array
     */
    protected $providers = [];

    /**
     * Lista de facades
     * @var array
     */
    protected $facades = [];

    /**
     * Lista de provider para trocar instancias
     * @var array
     */
    protected $instances = [];

    /**
     * Create do provider
     * @param \Illuminate\Contracts\Foundation\Application $app
     */
    public function __construct($app)
    {
        parent::__construct($app);

        // Trocar instancias
        foreach ($this->instances as $provider => $classServiceProvider)
        {
            // Limpar facade
            Facade::clearResolvedInstance($provider);

            // Trocar / Criar
            $this->app->instance($provider, new $classServiceProvider($app));
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Registrar providers
        foreach ($this->providers as $id => $class)
        {
            $app = $this->app;
            $this->app->singleton($id, function() use ($class, $app) {
                return $app->make($class);
            });
        }

        // Registrar comandos
        foreach ($this->commands as $provider => $classCommandProvider)
        {
            $this->app->singleton(sprintf('command.%s', $provider), function() use ($classCommandProvider){
                return new $classCommandProvider();
            });
        }
        $this->commands(Str::format(array_keys($this->commands), 'command.%s'));

        // Booting
        $this->app->booting(function () {

            if (class_exists('Illuminate\Foundation\AliasLoader'))
            {
                $loader = \Illuminate\Foundation\AliasLoader::getInstance();

                // Facdes
                foreach ($this->facades as $facade_id => $facade_class)
                {
                    $loader->alias($facade_id, $facade_class);
                }
            }
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        $list = [];

        // Carregar lista de provider dos comandos
        $list = array_merge($list, Str::format(array_keys($this->commands), 'command.%s'));

        // Carregar lista de providers
        $list = array_merge($list, array_keys($this->providers));

        return $list;
    }
}