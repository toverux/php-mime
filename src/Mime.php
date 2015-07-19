<?php

namespace Alembic\Mime;

class Mime
{
    const DBPATH = __DIR__.'/mimedb.basic.php';

    const DBPATH_EXTENDED = __DIR__.'/mimedb.extended.php';

    public static $dbLoaded = false;

    protected static $m2e = [];

    protected static $e2m = [];

    /**
     * @api
     * @var mixed Mime fallback when an extension is not found.
     */
    public static $defaultType = 'application/octet-stream';

    /**
     * @api
     * @var mixed Extension fallback when a mime type is not found.
     */
    public static $defaultExtension = null;


    /**
     * Lookup a mime type based on extension.
     * $path could be:
     *  - "dir/file.ext"
     *  - "file.ext"
     *  - ".EXT"
     *  - "ext"
     * @api
     * @param string $path
     * @param string $fallback If the extension cannot be found.
     *                         Defaulting to $defaultType.
     * @return string
     */
    public static function lookup($path, $fallback = false)
    {
        if($fallback === false) $fallback = self::$defaultType;

        if(($pos = strrpos($path, '.')) !== false) {
            $ext = substr($path, $pos+1);
        } elseif(($pos = strrpos($path, '/')) !== false) {
            return $fallback;
        } else {
            $ext = $path;
        }

        $ext = strtolower($ext);

        if(!self::$dbLoaded) self::loadDatabase();

        return isset(self::$e2m[$ext])
                   ? self::$e2m[$ext]
                   : $fallback;
    }

    /**
     * Lookup an extension based on mime type.
     *
     * @api
     * @param string $type
     * @return string
     */
    public static function extension($type)
    {
        if(!self::$dbLoaded) self::loadDatabase();

        return isset(self::$m2e[$type])
                   ? self::$m2e[$type][0]
                   : self::$defaultExtension;
    }

    /**
     * Defines new mime types and related extensions.
     *
     * @api
     * @param array $m2e Assoc array of format [mime=>[ext1, ext2, ...]]
     * @return void
     */
    public static function define(array $m2e)
    {
        foreach($m2e as $mime => $extensions) {
            if(is_string($extensions)) {
                $extensions = [$extensions];
            }

            self::$m2e[$mime] = $extensions;

            foreach($extensions as $extension) {
                self::$e2m[$extension] = $mime;
            }
        }
    }

    /**
     * Loads an Apache .types file or a Nginx file
     * containing a types block.
     *
     * @api
     * @param string $filepath The resource path or URL
     * @return void
     */
    public static function load($filepath)
    {
        if(($content = @file_get_contents($filepath)) === false) {
            throw new \RuntimeException(sprintf('Unable to read content of resource %s', $filepath));
        }

        $m2e = [];

        # Nginx format
        if(preg_match('/\s*types\s*{(\X*)}\s*/', $content, $matches)) {
            $content = $matches[1];
        }

        # After removing Nginx definition block,
        # Nginx and Apache basically share the same syntax.
        foreach(explode("\n", $content) as $rawline) {
            $rawline = trim($rawline, "; \t\n\r\0\x0B");
            if(empty($rawline) || $rawline[0] == '#') continue;

            $extensions = preg_split('/[\s]+/', $rawline);
            $mime = $extensions[0];
            array_shift($extensions);

            $m2e[$mime] = $extensions;
        }

        self::define($m2e);
    }

    /**
     * Loads the extended database containing the Apache
     * types (hundreds of supplementary entries).
     *
     * @api
     * @return void
     */
    public static function apacheExtend()
    {
        self::loadDatabase(self::DBPATH_EXTENDED);
    }

    /**
     * Loads a PHP-Mime PHP database.
     *
     * @param string $dbpath
     * @return void
     */
    protected static function loadDatabase($dbpath = self::DBPATH)
    {
        $datas = require $dbpath;
        self::$m2e = &$datas[0];
        self::$e2m = &$datas[1];

        self::$dbLoaded = true;
    }
}
