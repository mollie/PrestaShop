<?php

namespace MolliePrefix;

// Don't redefine the functions if included multiple times.
if (!\function_exists('MolliePrefix\\GuzzleHttp\\Psr7\\str')) {
    require __DIR__ . '/functions.php';
}
