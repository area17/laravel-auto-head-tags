<?php

namespace A17\LaravelAutoHeadTags\Tests;

use A17\LaravelAutoHeadTags\Head;
use Illuminate\Support\Facades\Blade;

class HeadTest extends TestCase
{
    public function testCanLoadConfig()
    {
        $this->assertEquals(
            '$config',
            config('laravel-auto-head-tags.config.key')
        );
    }

    public function testCanCompileBladeExtension()
    {
        $bladeSnippet = '@head';

        $expectedCode =
            '<?php echo (new A17\LaravelAutoHeadTags\Head($__data))->render(); ?>';

        $this->assertEquals($expectedCode, Blade::compileString($bladeSnippet));
    }

    public function testCanRenderHead()
    {
        $rendered = $this->head->render();

        $this->assertStringContainsString(
            '<meta name="twitter:title" content="OG - Your page title" />',
            $rendered
        );
    }
}
