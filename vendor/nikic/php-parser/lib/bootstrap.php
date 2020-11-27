<?php

namespace MolliePrefix;

if (!\class_exists('MolliePrefix\\PhpParser\\Autoloader')) {
    require __DIR__ . '/PhpParser/Autoloader.php';
}
\MolliePrefix\PhpParser\Autoloader::register();
