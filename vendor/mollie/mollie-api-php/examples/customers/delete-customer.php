<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * Delete a customer from the Mollie API.
 */

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use function htmlspecialchars;

try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";
    $mollie->customers->delete("cst_fE3F6nvX");
    echo "<p>Customer deleted!</p>";
} catch (ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}
