<?php

namespace _PhpScoper5ea00cc67502b;

/*
 * How to create an on-demand recurring payment.
 */

use _PhpScoper5ea00cc67502b\Mollie\Api\Exceptions\ApiException;
use _PhpScoper5ea00cc67502b\Mollie\Api\Types\SequenceType;
use function dirname;
use function htmlspecialchars;
use function strcasecmp;
use function time;

try {
    /*
     * Initialize the Mollie API library with your API key or OAuth access token.
     */
    require "../initialize.php";
    /*
     * Retrieve the last created customer for this example.
     * If no customers are created yet, run the create-customer example.
     */
    $customer = $mollie->customers->page(null, 1)[0];
    /*
     * Generate a unique order id for this example.
     */
    $orderId = time();
    /*
     * Determine the url parts to these example files.
     */
    $protocol = isset($_SERVER['HTTPS']) && strcasecmp('off', $_SERVER['HTTPS']) !== 0 ? "https" : "http";
    $hostname = $_SERVER['HTTP_HOST'];
    $path = dirname(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF']);
    /**
     * Customer Payment creation parameters.
     *
     * @See: https://docs.mollie.com/reference/v2/customers-api/create-customer-payment
     */
    $payment = $customer->createPayment([
        "amount" => [
            "value" => "10.00",
            // You must send the correct number of decimals, thus we enforce the use of strings
            "currency" => "EUR",
        ],
        "description" => "On-demand payment - Order #{$orderId}",
        "webhookUrl" => "{$protocol}://{$hostname}{$path}/payments/webhook.php",
        "metadata" => ["order_id" => $orderId],
        // Flag this payment as a recurring payment.
        "sequenceType" => SequenceType::SEQUENCETYPE_RECURRING,
    ]);
    /*
     * In this example we store the order with its payment status in a database.
     */
    database_write($orderId, $payment->status);
    /*
     * The payment will be either pending or paid immediately. The customer
     * does not have to perform any payment steps.
     */
    echo "<p>Selected mandate is '" . htmlspecialchars($payment->mandateId) . "' (" . htmlspecialchars($payment->method) . ").</p>\n";
    echo "<p>The payment status is '" . htmlspecialchars($payment->status) . "'.</p>\n";
} catch (ApiException $e) {
    echo "API call failed: " . htmlspecialchars($e->getMessage());
}
