<?php

namespace A17\TwillMetas;

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
            __DIR__ . '/../config/twill-metas.php',
            'twill-metas',
        );
    }

    public function publishConfig()
    {
        $this->publishes(
            [
                __DIR__ . '/../config/twill-metas.php' => config_path(
                    'twill-metas.php',
                ),
            ],
            'config',
        );
    }

    public function configureBlade()
    {
        Blade::directive('twill-metas', function ($expression) {
            return "<?php echo 'metas'; ?>";
        });
    }
}
