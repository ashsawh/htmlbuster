<?php

namespace Sawh\HtmlBuster;

use Illuminate\Support\ServiceProvider;

class BusterServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHtmlBuilder();

        $this->app->alias('buster', 'Sawh\HtmlBuster\BusterBuilder');
    }

    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app->singleton('buster', function ($app) {
            return new BusterBuilder($app['url'], $app['view']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['buster', 'Sawh\HtmlBuster\BusterBuilder'];
    }
}
