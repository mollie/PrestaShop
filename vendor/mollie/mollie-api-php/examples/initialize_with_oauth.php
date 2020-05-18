<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * Make sure to disable the display of errors in production code!
 */

use _PhpScoper5ea00cc67502b\Mollie\Api\MollieApiClient;
use function error_reporting;
use function ini_set;
use const E_ALL;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/functions.php";
/*
 * Initialize the Mollie API library with OAuth.
 *
 * See: https://docs.mollie.com/oauth/overview
 */
$mollie = new MollieApiClient();
$mollie->setAccessToken("access_Wwvu7egPcJLLJ9Kb7J632x8wJ2zMeJ");
