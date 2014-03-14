A PHP library for implementing Tin Can API.

For hosted API documentation, basic usage instructions, supported version listing, etc. visit the main project website at:

http://rusticisoftware.github.io/TinCanPHP/

For more information about the Tin Can API visit:

http://tincanapi.com/

Requires PHP 5.4 or later.

### Installation

TinCanPHP is available via [Composer](http://getcomposer.org).

```
php composer.phar require rusticisoftware/tincan:~0.0
```

With the package installed require the Composer autoloader:

```php
require 'vendor/autoload.php';
```

### Testing

Tests are implemented using PHPUnit.

```
phpunit tests
```

### API Doc Generation

Documentation can be output using [phpDocumentor2](http://phpdoc.org). It will be installed when using Composer. To generate documentation:

```
./vendor/bin/phpdoc.php
```

From the root of the repository after running `php composer.phar update`. Documentation will be output to `doc/api`.

If you do not have the default timezone set in your `php.ini` file you can create one in the base of the repo and use the `PHPRC` environment variable to point to it. Use something like:

```
export PHPRC="/path/to/repos/TinCanPHP/php.ini"
```

And set the timezone in that file using:

```
[PHP]

date.timezone = "US/Central"
```
