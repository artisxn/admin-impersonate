<?php

namespace codicastudio\NovaImpersonate;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Nova::script('admin-impersonate', __DIR__.'/../dist/js/field.js');
        Nova::style('admin-impersonate', __DIR__.'/../dist/css/field.css');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'admin-impersonate');

        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/admin-impersonate'),
        ], 'admin-impersonate-views');

        $this->publishes([
            __DIR__.'/../config/admin-impersonate.php' => config_path('admin-impersonate.php'),
        ], 'admin-impersonate-config');

        $this->app->booted(function () {
            if (config('admin-impersonate.enable_middleware')) {
                $this->app['Illuminate\Contracts\Http\Kernel']->pushMiddleware(\codicastudio\NovaImpersonate\Http\Middleware\Impersonate::class);
            }
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            //
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        if (config('admin-impersonate.enable_routes', true)) {
            Route::middleware(Arr::wrap(config('admin-impersonate.middleware.base')))
                ->prefix('admin-impersonate')
                ->name('nova.impersonate.')
                ->group(__DIR__.'/../routes/api.php');
        }
    }

    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/admin-impersonate.php', 'admin-impersonate');
    }
}
