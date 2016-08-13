# An Intelligent Web Scraper for Laravel 5

## Installation

This package can be installed via Composer:

``` bash
composer require jeroenherczeg/hyena
```

## Usage

```php
$result = Hyena::visit('google.be')->extract(['name', 'images']);
```

```php
[
  'name' => 'Google',
  'images' => [
    'https://www.google.be/images/nav_logo242_hr.png',
    'http://ssl.gstatic.com/gb/images/p1_a4541be8.png',
    'https://www.google.be/logos/doodles/2016/2016-doodle-fruit-games-day-9-5664146415681536-res.png',
    'https://www.google.be/logos/doodles/2016/2016-doodle-fruit-games-day-9-5664146415681536-hp.gif',
  ]
]
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.


## Security

If you discover any security related issues, please email jeroen@herczeg.be instead of using the issue tracker.

## About Jeroen Herczeg
Hello. I can help you or your team with my broad knowledge of frontend & backend web technologies and tools. I'm an eager learner and easily adapt to new environments. Work remotely? I like that. Need me on location? I'm sure we'll figure something out! jeroen@herczeg.be

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
