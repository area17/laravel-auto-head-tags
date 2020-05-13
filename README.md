# Laravel Auto Head Tags

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

## Description

This package aims to ease the creating of all HTML head tags: meta, links, SEO, Open Graph, Twitter and whatever else you need. 

The package will grab all the info it needs from the data sent to Blade:

``` php
return view('welcome', [
    'seo' => [
        'title' => 'Your page title',

        'description' => 'The meta description for the page',

        'urls' => [
            'canonical' => 'https://site.com/the-article-slug'
        ],
    ],

    'twitter' => [
        'handle' => '@opticalcortex'
    ],

    'image' => [
        'url' => 'https://site.com/image.jpg'
    ]
]);
```

To generate, out of the box, this set of tags for you:

``` html
<title>Your page title</title>\n
<meta charset="utf-8" />\n
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />\n
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />\n
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1" />\n
<meta name="mobile-web-app-capable" content="yes" />\n
<meta name="apple-mobile-web-app-capable" content="yes" />\n
<meta name="apple-mobile-web-app-title" content="Your page title" />\n
<meta name="description" content="The meta description for the page" />\n
<meta name="msapplication-TileColor" content="#ffffff" />\n
<meta name="msapplication-config" content="/favicon/browserconfig.xml" />\n
<meta name="theme-color" content="#ffffff" />\n
<meta itemprop="description" content="The meta description for the page" />\n
<meta property="og:type" content="website" />\n
<meta property="og:title" content="Your page title" />\n
<meta property="og:site_name" content="Laravel" />\n
<meta property="og:description" content="The meta description for the page" />\n
<meta property="og:url" content="https://site.com/the-article-slug" />\n
<meta property="og:image:width" content="1200" />\n
<meta property="og:image:height" content="630" />\n
<meta name="twitter:title" content="Your page title" />\n
<meta name="twitter:site" content="@opticalcortex" />\n
<meta name="twitter:description" content="The meta description for the page" />\n
<meta name="twitter:url" content="https://site.com/the-article-slug" />\n
<meta name="twitter:card" content="summary_large_image" />\n
<meta name="twitter:creator" content="@opticalcortex" />\n
<link rel="manifest" href="/favicon/site.webmanifest" />\n
<link rel="icon" href="/favicon/favicon.ico" />\n
<link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png" />\n
<link rel="icon" type="image/png" sizes="16x16" />\n
<link rel="apple-touch-icon" sizes="180x180" href="/favicon/apple-touch-icon.png" />\n
<link rel="mask-icon" href="/favicon/safari-pinned-tab.svg" color="#1d1d1d" />\n
<link rel="shortcut icon" href="/favicon/favicon.ico" />\n
<link rel="canonical" href="https://site.com/the-article-slug" />
```

[The list of tags is larger](https://github.com/area17/laravel-auto-head-tags/blob/master/config/laravel-auto-head-tags.yaml), but if you don't provide enough information to create those tags, they won't be created.

## Install

### Via Composer

``` bash
composer require area17/laravel-auto-head-tags
```

### Publish the config file

``` bash
php artisan vendor:publish --provider="A17\LaravelAutoHeadTags\ServiceProvider"
```

# Using

## Add to your blade template

Add the tag `@head` to your main template:

``` html
<!DOCTYPE html>
<html lang="{{ locale() }}">
    <head>
        @head

        ...
    </head>

    ...
</html>
``` 

This directive is also configurable, just publish the configuration and change the `blade.directive`.

## Configuring 

All available tags are on the config file: `config/laravel-auto-head-tags.yaml`, and everything is configurable. This is an extract of the tags section:

``` yaml
# TAGS
tags:
    # META

    # HEAD
    meta:
    - charset: "{head.charset}|utf-8"
    - name: viewport
      content: "{head.viewport}|width=device-width, initial-scale=1.0, minimum-scale=1"
    - name: author
      content: "{head.author}"
    - http-equiv: "X-UA-Compatible"
      content: "{head.httpEquiv}|IE=Edge"
    - name: mobile-web-app-capable
      content: "{head.mobile-web-app-capable}|yes"
    - name: apple-mobile-web-app-capable
      content: "{head.apple-mobile-web-app-capable}|yes"
    - name: apple-mobile-web-app-title
      content: "{head.apple-mobile-web-app-title}|{og.title}|{$config.app.name}"
```

You can define macros to access Blade data, using `{}`, and the package can resolve them using the "dot" notation for arrays:

``` yaml
content: "{head.author}"
```

You can define as many fallbacks you need for those macros:

``` yaml
title: "{title}|{head.title}|{seo.title}|{og.title}|{$config.app.name}"
```

And you can also access data from the Laravel config:

``` yaml
content: "{$config.app.name}"
```

If it's required to generate more than one URL for a single tag definition, there's `loop` concept:

``` yaml
- rel: canonical
  href: "{seo.urls.canonical}"
- rel: alternate
  href: "{seo.urls.hreflang:value}"
  hreflang: "{seo.urls.hreflang:key}"
```

If `seo.urls.hreflang` is an array made of locales (`key`) and URLs (`value`), this configuration will generate these tags:

``` html
<link rel="canonical" href="http://site.com/fr/events/event-slug" />
<link rel="alternate" href="http://site.com/fr/evenements/event-slug" hreflang="fr" />
<link rel="alternate" href="http://site.com/en/events/event-slug" hreflang="en" />
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email antonio@area17.com instead of using the issue tracker.

## Credits

- [AREA 17](https://github.com/area17)
- [Antonio Ribeiro][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/area17/laravel-auto-head-tags.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/area17/laravel-auto-head-tags/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/area17/laravel-auto-head-tags.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/area17/laravel-auto-head-tags.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/area17/laravel-auto-head-tags.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/area17/laravel-auto-head-tags
[link-travis]: https://travis-ci.com/github/area17/laravel-auto-head-tags
[link-scrutinizer]: https://scrutinizer-ci.com/g/area17/laravel-auto-head-tags/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/area17/laravel-auto-head-tags
[link-downloads]: https://packagist.org/packages/area17/laravel-auto-head-tags
[link-author]: https://github.com/antonioribeiro
[link-contributors]: ../../contributors
