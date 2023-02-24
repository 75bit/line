<?php

namespace Line;

use Monolog\Logger;
use Line\Commands\TestCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        // Publish configuration file
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/line.php' => config_path('line.php'),
            ]);
        }

        // Register views
        $this->app['view']->addNamespace('line', __DIR__ . '/../resources/views');

        // Register facade
        if (class_exists(\Illuminate\Foundation\AliasLoader::class)) {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Line', 'Line\Facade');
        }

        // Register commands
        $this->commands([
            TestCommand::class,
        ]);

        // Map any routes
        $this->mapLineApiRoutes();

        // Create an alias to the line-js-client.blade.php include
        Blade::include('line::line-js-client', 'lineJavaScriptClient');
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/line.php', 'line');

        $this->app->singleton('line', function ($app) {
            return new Line(new \Line\Http\Client(
                config('line.login_key', 'login_key'),
                config('line.project_key', 'project_key')
            ));
        });

        if ($this->app['log'] instanceof \Illuminate\Log\LogManager) {
            $this->app['log']->extend('line', function ($app, $config) {
                $handler = new \Line\Logger\LineHandler(
                    $app['line']
                );

                return new Logger('line', [$handler]);
            });
        }
    }

    protected function mapLineApiRoutes()
    {
        Route::group(
            [
                'namespace' => '\Line\Http\Controllers',
                'prefix' => 'line-api'
            ],
            function ($router) {
                require __DIR__ . '/../routes/api.php';
            }
        );
    }
}
