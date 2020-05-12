<?php

namespace A17\LaravelAutoHeadTags\Tests;

use A17\LaravelAutoHeadTags\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
