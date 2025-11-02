# PHP pure and simple VDOM

[![Latest Version on Packagist](https://img.shields.io/packagist/v/icetea/icedom.svg?style=flat-square)](https://packagist.org/packages/icetea/icedom)
[![Tests](https://img.shields.io/github/actions/workflow/status/icetea/icedom/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/icetea/icedom/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/icetea/icedom.svg?style=flat-square)](https://packagist.org/packages/icetea/icedom)

**IceDOM** is a pure PHP library for building HTML documents using a Virtual DOM-like approach. Write HTML in PHP with a fluent, type-safe API—no templates required. All content is automatically escaped to prevent XSS attacks.

## Key Features

- 🔒 **Automatic XSS Protection** - All content is escaped by default
- 🎯 **Type-Safe** - Full IDE autocomplete and type hints
- 🔄 **Virtual DOM-like** - Build HTML using PHP objects and methods
- 🎨 **Fluent API** - Chain methods for clean, readable code
- 🚀 **Zero Dependencies** - Pure PHP, no external libraries
- 📦 **150+ HTML Tags** - All standard HTML5 elements included

## Quick Example

```php
use function IceTea\IceDOM\{_div, _h1, _p, _button};

$card = _div(['class' => 'card'], [
    _h1('Welcome to IceDOM'),
    _p('Build HTML with PHP, fluently and safely.'),
    _button(['class' => 'btn-primary'], 'Get Started'),
]);

echo $card;
// Output: <div class="card"><h1>Welcome to IceDOM</h1>...</div>
```

## Installation

You can install the package via composer:

```bash
composer require icetea/icedom
```

## Usage

Read full usage document here : [Usage](./USAGE.md)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- Ideas, Write Core Classes, Review Docs - Tests : [KhanhIceTea](https://github.com/khanhicetea)
- PHPDocs, Tests : LLMs (because it writes these better and faster than me)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
