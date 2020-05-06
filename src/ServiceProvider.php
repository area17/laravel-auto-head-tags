<?php

namespace A17\TwillHead;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    public function boot()
    {
        $this->publishConfig();

        $this->configureBlade();
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/twill-head.php',
            'twill-head',
        );
    }

    public function publishConfig()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/twill-head.php' => config_path(
                    'twill-head.php',
                ),
            ],
            'config',
        );
    }

    public function configureBlade()
    {
        Blade::directive('twillhead', function ($expression) {
            return Head::render();
        });
    }
}
