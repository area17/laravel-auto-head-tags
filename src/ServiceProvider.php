<?php

namespace A17\LaravelAutoHeadTags;

use PragmaRX\Yaml\Package\Yaml;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Boot
     */
    public function boot()
    {
        $this->publishConfig();

        $this->configureBlade();
    }

    /**
     * Register
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Publish config
     */
    public function publishConfig()
    {
        $this->publishes(
            [
                __DIR__ .
                '/../config/laravel-auto-head-tags.yaml' => config_path(
                    'laravel-auto-head-tags.yaml'
                )
            ],
            'config'
        );
    }

    /**
     * Configure blade
     */
    public function configureBlade()
    {
        Blade::directive(
            config('laravel-auto-head-tags.blade.directive'),
            function ($expression) {
                return '<?php echo (new A17\LaravelAutoHeadTags\Head($__data))->render(); ?>';
            }
        );
    }

    /**
     * Merge config
     */
    public function mergeConfig()
    {
        $app = config_path('laravel-auto-head-tags.yaml');

        $package = __DIR__ . '/../config/laravel-auto-head-tags.yaml';

        (new Yaml())->loadToConfig(
            file_exists($app) ? $app : $package,
            'laravel-auto-head-tags'
        );
    }
}
