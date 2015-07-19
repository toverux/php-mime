<?php

namespace Alembic\Mime;

class Mime
{
    const DBPATH = __DIR__.'/mimedb.basic.php';

    const DBPATH_EXTENDED = __DIR__.'/mimedb.extended.php';

    public static $dbLoaded = false;

    protected static $m2e = [];

    protected static $e2m = [];

    public static $defaultType = 'application/octet-stream';

    public static $defaultExtension = null;


    public static function lookup($path)
    {
        if(($pos = strrpos($path, '.')) !== false) {
            $ext = substr($path, $pos+1);
        } elseif(($pos = strrpos($path, '/')) !== false) {
            return self::$defaultType;
        } else {
            $ext = $path;
        }

        $ext = strtolower($ext);

        if(!self::$dbLoaded) self::loadDatabase();

        return isset(self::$e2m[$ext])
                   ? self::$e2m[$ext]
                   : self::$defaultType;
    }

    public static function extension($type)
    {
        if(!self::$dbLoaded) self::loadDatabase();

        return isset(self::$m2e[$type])
                   ? self::$m2e[$type][0]
                   : self::$defaultExtension;
    }

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

    public static function apacheExtend()
    {
        self::loadDatabase(self::DBPATH_EXTENDED);
    }

    protected static function loadDatabase($dbpath = self::DBPATH)
    {
        $datas = require $dbpath;
        self::$m2e = &$datas[0];
        self::$e2m = &$datas[1];

        self::$dbLoaded = true;
    }
}
