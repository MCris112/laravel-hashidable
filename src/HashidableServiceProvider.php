<?php

namespace Mcris112\LaravelHashidable;

use Illuminate\Support\ServiceProvider;

class HashidableServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/hashidable.php',
            'hashidable'
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            dirname(__DIR__) . '/config/hashidable.php' => config_path('hashidable.php'),
        ], 'hashidable.config');

        $this->registerValidationRules();
    }

    /**
     * Register custom validation rules.
     *
     * @return void
     */
    protected function registerValidationRules()
    {
        $this->app->make('validator')->extend('hashid_exists', function ($attribute, $value, $parameters, $validator) {
            if (count($parameters) < 1) {
                throw new \InvalidArgumentException("Validation rule hashid_exists requires at least 1 parameter (model).");
            }

            $model = $parameters[0];
            $column = $parameters[1] ?? null;

            return (new Rules\HashidExists($model, $column))->passes($attribute, $value);
        });
    }
}
