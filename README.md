# PHP-Mime

[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/e9103654-845f-40b7-8eeb-009e49e09067.svg?style=flat-square&label=insight)](https://insight.sensiolabs.com/projects/e9103654-845f-40b7-8eeb-009e49e09067)

Comprehensive MIME type mapping API, PHP clone of [broofa/node-mime](https://github.com/broofa/node-mime). MIME database from [jshttp/mime-db](https://github.com/jshttp/mime-db).

## Install

Install via [Composer](https://getcomposer.org/):

    composer install alembic/mime
    
## Usage

~~~php
use Alembic\Mime\Mime;
~~~

**Note:** All the methods exposed below can be called statically or with an instance of the `Mime` class (more practical in DI environments). Please note that, even when using instance calls, the MIMEs database is shared because it is static.


## API — Queries

### Mime::lookup($path)
Get the mime type associated with a file, if no mime type is found `application/octet-stream` is returned. Performs a case-insensitive lookup using the extension in `$path` (the substring after the last '.').  E.g.

```php
use Alembic\Mime\Mime;

Mime::lookup('/path/to/file.txt');         # => 'text/plain'
Mime::lookup('file.txt');                  # => 'text/plain'
Mime::lookup('.TXT');                      # => 'text/plain'
Mime::lookup('htm');                       # => 'text/html'
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

## API — Defining Custom Types

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
