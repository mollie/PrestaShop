<?php

namespace _PhpScoper5ece82d7231e4;

/*
 * Make sure to disable the display of errors in production code!
 */
\ini_set('display_errors', 1);
\ini_set('display_startup_errors', 1);
\error_reporting(\E_ALL);
require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/functions.php";
/*
 * Initialize the Mollie API library with your API key.
 *
 * See: https://www.mollie.com/dashboard/developers/api-keys
 */
$mollie = new \_PhpScoper5ece82d7231e4\Mollie\Api\MollieApiClient();
$mollie->setApiKey("test_dHar4XY7LxsDOtmnkVtjNVWXLSlXsM");
