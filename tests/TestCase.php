<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // Abort before RefreshDatabase can migrate or clear any real database.
        if (! $app->environment('testing')
            || $app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \LogicException('Tests require APP_ENV=testing and an isolated SQLite :memory: database. No database changes were made.');
        }

        return $app;
    }
}
