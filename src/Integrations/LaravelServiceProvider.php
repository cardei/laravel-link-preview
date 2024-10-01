<?php

namespace Cardei\LinkPreview\Integrations;

use Illuminate\Support\ServiceProvider;
use Cardei\LinkPreview\Client;

/**
 * Class LaravelServiceProvider
 * @package Cardei\LinkPreview\Integrations
 * @codeCoverageIgnore
 */
class LaravelServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // Publicar el archivo de configuración
        $this->publishes([
            __DIR__ . '/../config/config.php' => config_path('link-preview.php'),
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Fusionar la configuración del paquete con la de la aplicación
        $this->mergeConfigFrom(
            __DIR__ . '/../config/config.php', 'link-preview'
        );

        // Registrar el singleton 'link-preview' en el contenedor de servicios
        $this->app->singleton('link-preview', function() {
            return new Client();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['link-preview'];
    }
}
