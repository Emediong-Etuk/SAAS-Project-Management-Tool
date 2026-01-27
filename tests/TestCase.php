<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{

    protected static $migrationsRun = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$migrationsRun) {
            $this->artisan('migrate:fresh');
            static::$migrationsRun = true;
        }
    }
}
