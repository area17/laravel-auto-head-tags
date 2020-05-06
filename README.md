# Twill Head

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This package allows you to create transformers to generate view data for your Twill app. It contains a base Transformer 
class and a series of traits, allowing you not only to transform model data, but also generate all blocks, from Twill's block editor and preview data.

## Reasoning

The main class of this package was extracted from the work we did for a client where we decided to use Storybook and Twig templates 
to build the front end. The idea is to free the back end developer from writing front-end code. For this to happen, the whole data
generation is automated, starting from the controller `view()` call.

## Install

### Via Composer

``` bash
composer require area17/twill-head
```

### Publish the config file

``` bash
php artisan vendor:publish --provider="A17\TwillHead\ServiceProvider"
```

# ADD DOCUMENTATION!

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

[ico-version]: https://img.shields.io/packagist/v/area17/twill-head.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/area17/twill-head/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/area17/twill-head.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/area17/twill-head.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/area17/twill-head.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/area17/twill-head
[link-travis]: https://travis-ci.org/area17/twill-head
[link-scrutinizer]: https://scrutinizer-ci.com/g/area17/twill-head/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/area17/twill-head
[link-downloads]: https://packagist.org/packages/area17/twill-head
[link-author]: https://github.com/antonioribeiro
[link-contributors]: ../../contributors
