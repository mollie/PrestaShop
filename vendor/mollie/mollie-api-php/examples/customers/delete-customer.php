<?php

namespace _PhpScoper5eddef0da618a;

/*
 * Delete a customer from the Mollie API.
 */
try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";
    $mollie->customers->delete("cst_fE3F6nvX");
    echo "<p>Customer deleted!</p>";
} catch (\_PhpScoper5eddef0da618a\Mollie\Api\Exceptions\ApiException $e) {
    echo "API call failed: " . \htmlspecialchars($e->getMessage());
}
