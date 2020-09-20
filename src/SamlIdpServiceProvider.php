<?php

namespace ZiaKhan\SamlIdp;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SamlIdpServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot(Router $router)
    {
        $this->registerRoutes();
        $this->registerResources();
        $this->registerBladeComponents();
        $this->registerMigrations();

        $this->app->singleton('SamlIdp', function ($app) {
            return new SamlIdpService;
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();
        $this->offerPublishing();
        $this->registerServices();
    }

    /**
     * Configure the service provider
     *
     * @return void
     */
    private function configure()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/samlidp.php', 'samlidp');
    }

    /**
     * Offer publishing for the service provider
     *
     * @return void
     */
    public function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/samlidp'),
            ], 'samlidp_views');

            $this->publishes([
                __DIR__ . '/../config/samlidp.php' => config_path('samlidp.php'),
            ], 'samlidp_config');

            // Create storage/samlidp directory
            if (!file_exists(storage_path() . "/samlidp")) {
                mkdir(storage_path() . "/samlidp", 0755, true);
            }
        }
    }

    /**
     * Register blade components for service provider
     *
     * @return void
     */
    public function registerBladeComponents()
    {
        Blade::directive('samlidp', function ($expression) {
            if (request()->filled('SAMLRequest')) {
                return "<?php echo view('samlidp::components.input'); ?>";
            }
        });
    }

    /**
     * Register the application bindings.
     *
     * @return void
     */
    private function registerServices()
    {
    }

    /**
     * Register routes for the service provider
     *
     * @return void
     */
    private function registerRoutes()
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
    }

    /**
     * Register resources for the service provider
     *
     * @return void
     */
    private function registerResources()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'samlidp');
    }

    /**
     * Register migration files.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        if ($this->app->runningInConsole()) {
            return $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }
}
