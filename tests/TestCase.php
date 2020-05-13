<?php

namespace A17\LaravelAutoHeadTags\Tests;

use A17\LaravelAutoHeadTags\Head;
use A17\LaravelAutoHeadTags\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * @var \A17\LaravelAutoHeadTags\Head
     */
    protected $head;

    protected $data = [
        'seo' => [
            'title' => 'SEO - Your page title',

            'description' => 'The meta description for the page',

            'urls' => [
                'canonical' => 'https://site.com/the-article-slug',

                'hreflang' => [
                    'fr' => 'https://site.com/fr/the-article-slug',
                    'en' => 'https://site.com/en/the-article-slug'
                ]
            ]
        ],

        'twitter' => [
            'handle' => '@opticalcortex'
        ],

        'image' => [
            'url' => 'https://site.com/image.jpg'
        ],

        'og' => [
            'title' => 'OG - Your page title',

            'site-name' => 'App name',

            'locale' => ['current' => 'fr', 'alternate' => ['fr', 'en']],

            'type' => null,

            'description' => 'Description',

            'url' => 'https://site.com/article-slug',

            'image' => [
                'url' => 'https://site.com/article-slug/image.jpg',

                'secure-url' => 'https://site.com/article-slug/image.jpg',

                'alt' => 'Image alt',

                'type' => 'image/jpeg',

                'width' => '800',

                'height' => '600'
            ],

            'video' => [
                'url' => 'http://site.com/article-slug/video.mp4',

                'secure-url' => 'https://site.com/article-slug/video.mp4',

                'alt' => 'Video alt',

                'type' => 'text/html'
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->head = new Head($this->data);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
