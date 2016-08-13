# An Intelligent Web Scraper for Laravel 5

## Installation

This package can be installed via Composer:

``` bash
composer require jeroenherczeg/hyena
```

## Usage

```php
$result = Hyena::visit('https://github.com/jeroenherczeg/hyena')->extract(['name', 'url', 'images']);
```

```php
[
  'name' => 'Github',
  'url' => 'https://github.com'
  'images' => [
    'https://avatars1.githubusercontent.com/u/759412?v=3&s=40',
    'https://assets-cdn.github.com/images/spinners/octocat-spinner-128.gif',
    'https://assets-cdn.github.com/images/spinners/octocat-spinner-32.gif'
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
