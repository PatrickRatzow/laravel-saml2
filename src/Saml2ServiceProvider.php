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
            __DIR__ . '/../config/' => config_path('saml2'),
        ], 'config');

        if (! class_exists('CreateSamlMessageTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_saml_message_table.php.stub' => database_path(
                    'migrations/' . date('Y_m_d_His', time()) . '_create_saml_message_table.php'
                ),
            ], 'migrations');
        }

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
