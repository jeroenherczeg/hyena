<?php
namespace Jeroenherczeg\Hyena;

use Illuminate\Support\ServiceProvider;
use Jeroenherczeg\Hyena\Hyena;

class HyenaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('hyena', function ($app) {
            return new Hyena();
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
