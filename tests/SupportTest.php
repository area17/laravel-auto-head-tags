<?php

namespace A17\LaravelAutoHeadTags\Tests;

use A17\LaravelAutoHeadTags\Head;
use Illuminate\Support\Facades\Blade;

class SupportTest extends TestCase
{
    public function testToArrayCanIgnoreNonArrays()
    {
        $this->assertEquals('test', to_array('test'));
    }

    public function testConfigCanReturnWholeConfig()
    {
        $this->assertTrue(isset($this->head->config()['blade']));
        $this->assertTrue(isset($this->head->config()['delimiters']));
    }
}
