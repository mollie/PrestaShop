<?php

define('_PS_VERSION_', getenv('PRESTASHOP_VERSION'));

include __DIR__.'/_functions.php';

spl_autoload_register(function ($class) {
    if (file_exists(__DIR__."/../_support/Fake/$class.php")) {
        include_once __DIR__."/../_support/Fake/$class.php";
        return true;
    }
    return false;
});

include __DIR__.'/../../mollie.php';
