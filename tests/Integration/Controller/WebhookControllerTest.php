<?php
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

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Mollie\Tests\Integration\BaseTestCase;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;

class WebhookControllerTest extends BaseTestCase
{
    /** @var Client */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = (new ClientFactory())->getClient([
            'base_uri' => __PS_BASE_URI__,
            'timeout' => 15,
        ]);
    }

    public function testItRespondsBadReqiestWithoutTimeout()
    {
        $result = $this->client->sendRequest(
            new Request('GET', Context::getContext()->link->getModuleLink('mollie', 'webhook'))
        );

        $this->assertEquals(400, $result->getStatusCode());
    }
}
