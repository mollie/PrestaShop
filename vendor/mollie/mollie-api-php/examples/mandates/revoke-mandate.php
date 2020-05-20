<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * Revoke a customer mandate
 */

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use function htmlspecialchars;

try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";
    /*
     * Retrieve an existing customer by his customerId
     */
    $customer = $mollie->customers->get("cst_cUa8HjKBus");
    /*
     * Retrieve an existing mandate by his mandateId
     */
    $mandate = $customer->getMandate("mdt_pa3s7rGnrC");
    /*
     * Revoke the mandate
     */
    $mandate->revoke();
    echo "<p>Mandate has been successfully revoked.</p>";
} catch (ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}
