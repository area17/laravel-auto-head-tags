<?php

namespace A17\TwillHead\Tests;

use A17\TwillHead\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
