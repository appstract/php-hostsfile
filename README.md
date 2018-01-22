# PHP HostsFile

[![Latest Version on Packagist](https://img.shields.io/packagist/v/appstract/php-hostsfile.svg?style=flat-square)](https://packagist.org/packages/appstract/php-hostsfile)
[![Total Downloads](https://img.shields.io/packagist/dt/appstract/php-hostsfile.svg?style=flat-square)](https://packagist.org/packages/appstract/php-hostsfile)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Read & Write Entries From the HostsFile with PHP.

## Installation

You can install the package via composer:

```bash
composer require appstract/php-hostsfile
```

## Usage

```php
$host = new Appstract\HostsFile\Processor($path); // optional path to the file

$host->getLines(); // get all lines in the hostsfile
$host->addLine($ip, $domain, $aliases); // add a new line to the hostsfile
$host->set($ip, $domain, $aliases); // add a new line and overwrite any existing
$host->removeLine($domain); // remove a line from the hostsfile by domain
$host->save(); // save the changes to the hostsfile
```

## Contributing

Contributions are welcome, [thanks to y'all](https://github.com/appstract/php-hostsfile/graphs/contributors) :)

## About Appstract

Appstract is a small team from The Netherlands. We create (open source) tools for webdevelopment and write about related subjects on [Medium](https://medium.com/appstract). You can [follow us on Twitter](https://twitter.com/teamappstract), [buy us a beer](https://www.paypal.me/teamappstract/10) or [support us on Patreon](https://www.patreon.com/appstract).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
