<?php

namespace Rjvandoesburg\NovaUrlRewriteTemplating\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Rjvandoesburg\NovaUrlRewriteTemplating\Http\Controllers\TemplateController;

class RouteServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Route::macro('NovaUrlRewriteTemplates', function () {
            $nova = ltrim(config('nova.path'), '/');
            $pattern = "^(?!{$nova}|nova-api).*$";

            Route::any('/template-api/{resource}/{resourceId}', [TemplateController::class, 'resource']);
            Route::any('/template-api/{templateUrl?}', TemplateController::class)
                ->where('templateUrl', $pattern);
        });
    }
}
