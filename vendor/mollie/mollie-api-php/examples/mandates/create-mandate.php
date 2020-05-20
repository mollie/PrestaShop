<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * Create a customer mandate via the Mollie API.
 */

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Types\MandateMethod;
use function htmlspecialchars;

try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";
    /*
     * Retrieve the last created customer for this example.
     * If no customers are created yet, run create-customer example.
     */
    $customer = $mollie->customers->page(null, 1)[0];
    /*
     * Create a SEPA Direct Debit mandate for the customer
     */
    $mandate = $customer->createMandate(["method" => MandateMethod::DIRECTDEBIT, "consumerAccount" => 'NL34ABNA0243341423', "consumerName" => 'B. A. Example']);
    echo "<p>Mandate created with id " . $mandate->id . " for customer " . $customer->name . "</p>";
} catch (ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}
