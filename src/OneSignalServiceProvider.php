<?php
namespace KCE\OneSignal;

use Illuminate\Support\ServiceProvider;

class OneSignalServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $configPath = __DIR__ . '/../config/onesignal.php';

        $this->publishes([$configPath => config_path('onesignal.php')], 'config');
        $this->mergeConfigFrom($configPath, 'onesignal');
        if (class_exists('Laravel\Lumen\Application')) {
            $this->app->configure('onesignal');
        }
        $this->createAliases();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('onesignal', function ($app) {
            $config = $app['config']['onesignal'] ?: $app['config']['onesignal::config'];
            return new Client($config['app_id'], $config['rest_api_key'], $config['user_auth_key']);
        });

        $this->app->bind('onesignal-notification', function () {
            return new Notification(app('onesignal'));
        });

        $this->app->bind('onesignal-app', function () {
            return new OnesignalApp(app('onesignal'));
        });
    }

    public function provides()
    {
        return ['onesignal'];
    }

    private function createAliases()
    {
    }
}
