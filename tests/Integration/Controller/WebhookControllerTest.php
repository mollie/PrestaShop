<?php

use Mollie\Tests\Integration\BaseTestCase;

/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

class WebhookControllerTest extends BaseTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->client = new Client();
    }

    public function testItRespondsWithoutTimeout()
    {
        // make a curl client
        $client = new Client();
    }
}
