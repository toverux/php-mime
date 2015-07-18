<?php

use Alembic\Mime\Mime;

require __DIR__ . '/../vendor/autoload.php';

/***************
* MIME::LOOKUP *
***************/

# No extension found (contrarily to node-mime, we
# do not consider "file" as an extension).
# Fallback to Mime::$defaultType.
Mime::lookup('folder/file');
# => "application/octet-stream"

# All Mime's methods are instance-compatible
# Use this in a DI environment, for example.
(new Mime)->lookup('folder/file');

# The library takes the last extension.
Mime::lookup('file.txt.html');
# => "text/html"

# Since the extension begins after the last
# dot, this is the same example as above, but
# here you can also see that lookup is case-insensitive.
Mime::lookup('.TXT');
# => "text/plain"

# If no dot is found, the whole string is
# interpreted as the extension.
Mime::lookup('yml');
# => "text/plain"

/******************
* MIME::EXTENSION *
******************/

# Exact reverse operation of lookup()
Mime::extension('text/html');
# => "html"

Mime::extension('application/octet-stream');
# => "bin"

# Defaulting to Mime::$defaultExtension.
# Warning: Mime::$defaultExtension is set to NULL by default.
Mime::extension('unknown/mime');
# => null

/***************
* MIME::DEFINE *
***************/

Mime::define([
	# multiple extensions, pass by array
    'text/x-some-format'    => ['x-sf', 'x-sft', 'x-sfml'],
    # one extension, you could pass by string
    'application/x-my-type' => 'x-mt'
]);


/*************
* MIME::LOAD *
*************/

# Mime::load() calls Mime::define() automatically.

# Load Nginx-formatted mime.types
Mime::load(__DIR__.'/nginx.mime.types');

# Load Apache-formatted mime.types
Mime::load(__DIR__.'/nginx.mime.types');
