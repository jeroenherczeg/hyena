# A Web Scraper for Laravel 5

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is where your description should go. Try and limit it to a paragraph or two, and maybe throw in a mention of what
PSRs you support to avoid any confusion with users and contributors.

## Install

Via Composer

``` bash
$ composer require jeroenherczeg/hyena
```

Next, you must install the service provider:

``` php
// config/app.php
'providers' => [
    ...
    Jeroenherczeg\Hyena\HyenaServiceProvider::class,
];
```

And add facade:

``` php
// config/app.php
'aliases' => [
    ...
    Jeroenherczeg\Hyena\Facades\Hyena::class,
];
```

## Usage

``` php
$result = Hyena::visit('https://github.com')->extract(['name', 'images']);
$result = Hyena::visit('https://github.com')->extract(['name', 'images'], [
    'min_image_width'    => 50, // optional, minimal width of picture in px
    'min_image_height'   => 50, // optional, minimal height of picture in px
    'min_image_filesize' => 16000, // optional, minimal filesize of picture in bytes
    'limit_images'       => 10  // optional, max count of images to return
]);
```

``` php
[
  'name' => 'Github',
  'images' => [
    'https://avatars1.githubusercontent.com/u/759412?v=3&s=40',
    'https://assets-cdn.github.com/images/spinners/octocat-spinner-128.gif',
    'https://assets-cdn.github.com/images/spinners/octocat-spinner-32.gif'
  ]
]
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email jeroen@herczeg.be instead of using the issue tracker.

## Credits

- [Jeroen Herczeg][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/jeroenherczeg/hyena.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/jeroenherczeg/hyena/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/jeroenherczeg/hyena.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/jeroenherczeg/hyena.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/jeroenherczeg/hyena.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jeroenherczeg/hyena
[link-travis]: https://travis-ci.org/jeroenherczeg/hyena
[link-scrutinizer]: https://scrutinizer-ci.com/g/jeroenherczeg/hyena/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/jeroenherczeg/hyena
[link-downloads]: https://packagist.org/packages/jeroenherczeg/hyena
[link-author]: https://github.com/jeroenherczeg
[link-contributors]: ../../contributors
