<?php

namespace Aacotroneo\Saml2;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class Saml2ServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/settings.php' => config_path('saml2/settings.php'),
            __DIR__ . '/../config/onelogin.php' => config_path('saml2/onelogin.php'),
            __DIR__ . '/../config/sps.php'      => config_path('saml2/sps.php'),
            __DIR__ . '/../config/idps.php'     => config_path('saml2/idps.php'),
        ], 'config');

        if (app('saml2')->config()->setup_routes) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/routes.php');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/settings.php', 'saml2.settings');

        $this->app->singleton(Config::class, function (Application $app): Config {
            return new Config($app->config['saml2']);
        });

        $this->app->singleton('saml2', function (Application $app): Saml2 {
            return new Saml2($app[Config::class]);
        });
    }
}
