<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * How to get the currently activated payment methods for the Orders API.
 */

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use function htmlspecialchars;

try {
    /*
     * Initialize the Mollie API library with your API key.
     *
     * See: https://www.mollie.com/dashboard/developers/api-keys
     */
    require "../initialize.php";
    /*
     * Get all the activated methods for this API key.
     * To get methods that are compatible with the Orders API
     * we are passing the 'resource' parameter.
     */
    $methods = $mollie->methods->all(['resource' => 'orders']);
    foreach ($methods as $method) {
        echo '<div style="line-height:40px; vertical-align:top">';
        echo '<img src="' . htmlspecialchars($method->image->size1x) . '" srcset="' . htmlspecialchars($method->image->size2x) . ' 2x"> ';
        echo htmlspecialchars($method->description) . ' (' . htmlspecialchars($method->id) . ')';
        echo '</div>';
    }
} catch (ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}
