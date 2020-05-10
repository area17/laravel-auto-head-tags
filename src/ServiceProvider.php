<?php

namespace A17\TwillHead;

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
                __DIR__ . '/../config/twill-head.yaml' => config_path(
                    'twill-head.yaml'
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
        Blade::directive('twillhead', function ($expression) {
            return '<?php echo (new A17\TwillHead\Head($__data))->render(); ?>';
        });
    }

    /**
     * Merge config
     */
    public function mergeConfig()
    {
        $app = config_path('twill-head.yaml');

        $package = __DIR__ . '/../config/twill-head.yaml';

        (new Yaml())->loadToConfig(
            file_exists($app) ? $app : $package,
            'twill-head'
        );
    }
}
