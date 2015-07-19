#!/usr/bin/env php
<?php

const eol = PHP_EOL;

$dest = __DIR__.'/../src/mimedb.php';

$sources = [
    'https://raw.githubusercontent.com/jshttp/mime-db/master/src/nginx-types.json',
    'https://raw.githubusercontent.com/jshttp/mime-db/master/src/custom-types.json'
];
$apacheSource = 'https://raw.githubusercontent.com/jshttp/mime-db/master/src/apache-types.json';

array_shift($argv);
foreach($argv as $arg) {
    switch($arg) {
        case '--with-apache':
            $sources[] = $apacheSource;
            break;
        default:
            $dest = __DIR__.'/../'.$arg;
            break;
    }
}

$nExtensionLess = $nExtension = 0;
$e2m = $m2e = [];

echo sprintf('Importing from %d sources... (.=imported M=merge I=ignore)', count($sources));

foreach($sources as $source) {
    echo eol.' => '.$source.eol;

    $mimes = json_decode(file_get_contents($source));

    foreach($mimes as $mime => $mimeInfo) {
        if(isset($mimeInfo->extensions)) {
            $nExtension++;
            if(isset($m2e[$mime])) {
                $m2e[$mime] = array_unique(array_merge($m2e[$mime], $mimeInfo->extensions));
                echo 'M';
            } else {
                $m2e[$mime] = $mimeInfo->extensions;
                echo '.';
            }

            foreach($mimeInfo->extensions as $extension) {
                $e2m[$extension] = $mime;
            }
        } else {
            $nExtensionLess++; echo 'I';
        }
    }
}

echo eol.eol.sprintf(
    ' => Imported %d mimes of %d, %d were without extensions informations and were ignored.',
    $nExtension, $nExtension + $nExtensionLess, $nExtensionLess
).eol;


echo sprintf(' => Generating database in %s...', realpath($dest));

$e2mStr = $m2eStr = '';

foreach($m2e as $mime => $extensions) {
    foreach($extensions as &$extension) {
        $extension = sprintf('\'%s\'', $extension);
    }
    $m2eStr .= sprintf('\'%s\' => [%s],', $mime, implode(', ', $extensions));
}

foreach($e2m as $extension => $mime) {
    $e2mStr .= sprintf('\'%s\' => \'%s\',', $extension, $mime);
}

$code = sprintf('<?php return [[%s],[%s]];', $m2eStr, $e2mStr);

file_put_contents($dest, $code);

echo eol.'Done.'.eol;
