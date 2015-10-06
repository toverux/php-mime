# PHP-Mime

[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/e9103654-845f-40b7-8eeb-009e49e09067.svg?style=flat-square&label=insight)](https://insight.sensiolabs.com/projects/e9103654-845f-40b7-8eeb-009e49e09067)
[![Packagist (Composer)](https://img.shields.io/packagist/v/alembic/mime.svg?style=flat-square)](https://packagist.org/packages/alembic/mime)
[![Packagist (Composer)](https://img.shields.io/packagist/dt/alembic/mime.svg?style=flat-square)](https://packagist.org/packages/alembic/mime)
![MIT License](https://img.shields.io/packagist/l/alembic/mime.svg?style=flat-square)

Comprehensive MIME type mapping API, PHP clone of [broofa/node-mime](https://github.com/broofa/node-mime). MIME database from [jshttp/mime-db](https://github.com/jshttp/mime-db).

## Data sources
This library uses the mime-db repository. The updates are automatically pulled from their repository, using `bin/pull-mime-db.php [out] [--with-apache]`.

By default this library exposes merged nginx (**75** mimes) and mime-db "custom types" (**38** usable mimes) and extensions. Those are the most commons types of the Internet. The library also includes the Apache types. A lot of Apache types are, in most part, useless and they are very numerous (**765** usable mimes), so, in order to reduce the memory impact, Apache types are not loaded by default.

## Install & Usage

    composer install alembic/mime

```php
use Alembic\Mime\Mime;
```

**Note:** All the methods exposed below can be called statically or with an instance of the `Mime` class (more practical in DI environments). Please note that, even when using instance calls, the MIMEs database is shared because it is static.


## API — Queries

### Mime::lookup($path [, $fallback])
Get the mime type associated with a file, if no mime type is found `$fallback` (`Mime::$defaultType` by default, which is `application/octet-stream`) is returned. Performs a case-insensitive lookup using the extension in `$path` (the substring after the last '.').  E.g.

```php
use Alembic\Mime\Mime;

Mime::lookup('/path/to/file.txt');         # => 'text/plain'
Mime::lookup('file.txt');                  # => 'text/plain'
Mime::lookup('.TXT');                      # => 'text/plain'
Mime::lookup('htm');                       # => 'text/html'
Mime::lookup('unknown');                   # => 'application/octet-stream'
Mime::lookup('unknown', null);             # => null
# Instance mode:
(new Mime)->lookup('folder/file');         # => 'application/octet-stream'
```

### Mime::$defaultType
The mime type returned when `Mime::lookup` fails to find the extension searched for (default is the standard `application/octet-stream`).

### Mime::extension($type)
Get the preferred extension for `$type`.

```php
Mime::extension('text/html');                 # => 'html'
Mime::extension('application/octet-stream');  # => 'bin'
```

### Mime::$defaultExtension
The extension returned when `Mime::extension` fails to find the type searched for (**warning**, default is `null`).

## API — Defining Types

Custom type mappings can be added on a per-project basis via the following APIs.

### Mime::define()

Add custom mime/extension mappings.

```php
Mime::define([
    'text/x-some-format'      => ['x-sf', 'x-sft', 'x-sfml'],
    'application/x-my-type'   => ['x-mt', 'x-mtt'],
    'application/x-my-format' => 'x-mf', # string allowed for unique ext
    # etc ...
]);

Mime::lookup('x-sft');  # => 'text/x-some-format'
```

As said before, the first entry in the extensions array is returned by `Mime::extension()`. E.g.

```php
Mime::extension('text/x-some-format');  # => 'x-sf'
```

### Mime::load($filepath)

Load mappings from an Apache ".types" file or a Nginx file containing a `types` block. The format (Nginx or Apache) is automagically detected based on the content.
Since the library uses `file_get_contents()`, the `$filepath` argument could be a filesystem path, an FTP path, an URL, whatever.
If the file couldn't be loaded (wrong path or insufficient privileges), it throws a `\RuntimeException`.

```php
Mime::load('./my_project.types');
```
The Apache .types file or the Nginx `types` block format is simple — see the [examples](/examples) directory for examples.

### Mime::apacheExtend()

Loads the packaged database of merged mime-db+nginx+Apache MIME types and extensions. The basic database (mime-db+nginx) is loaded by default and weighs roughly 8 KiB, when the all-in-one database loaded by this method weighs more than 70 KiB.
