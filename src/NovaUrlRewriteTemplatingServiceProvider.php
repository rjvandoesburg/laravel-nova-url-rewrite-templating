<?php

namespace Rjvandoesburg\NovaUrlRewriteTemplating;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Rjvandoesburg\NovaTemplating\TemplateHelper;
use Rjvandoesburg\NovaUrlRewriteTemplating\Providers\RouteServiceProvider;

class NovaUrlRewriteTemplatingServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        TemplateHelper::macro('forModel', function (Model $model) {
            $modelName = Str::slug(class_basename($model));

            return [
                "{$modelName}-{$model->getKey()}",
                $modelName,
                'model',
                'index',
            ];
        });
    }

}
