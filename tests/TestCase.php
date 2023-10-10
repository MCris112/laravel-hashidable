<?php

namespace Mcris112\LaravelHashidable\Tests;

use Mcris112\LaravelHashidable\HashidableServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Orchestra\Database\ConsoleServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutExceptionHandling();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->withFactories(__DIR__ . '/database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ConsoleServiceProvider::class,
            HashidableServiceProvider::class,
        ];
    }
}
