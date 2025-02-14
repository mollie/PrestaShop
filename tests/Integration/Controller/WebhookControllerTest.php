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

    /**
     * @dataProvider webhookDataProvider
     */
    public function testWebhookResponse($url, $expectedStatusCode, $expectedResponseBody)
    {
        $result = $this->client->sendRequest(
            new Request('GET', $url)
        );

        $this->assertEquals($expectedStatusCode, $result->getStatusCode());
        $this->assertEquals($expectedResponseBody, $result->getBody()->getContents());
    }

    public function webhookDataProvider(): array
    {
        return [
            'Missing security token' => [
                'url' => Context::getContext()->link->getModuleLink('mollie', 'webhook'),
                'expectedStatusCode' => 400,
                'expectedResponseBody' => '{"success":false,"errors":["Missing security token"],"data":[]}',
            ],
            'Missing transaction id' => [
                'url' => Context::getContext()->link->getModuleLink('mollie', 'webhook') . '?' . http_build_query([
                        'security_token' => 'bad_token_value',
                    ]),
                'expectedStatusCode' => 422,
                'expectedResponseBody' => '{"success":false,"errors":["Missing transaction id"],"data":[]}',
            ],
            'Valid response' => [
                'url' => Context::getContext()->link->getModuleLink('mollie', 'webhook') . '?' . http_build_query([
                        'security_token' => 'token_value',
                        'transaction_id' => 'tr_01010101010101'
                    ]),
                'expectedStatusCode' => 422,
                'expectedResponseBody' => '{"success":false,"errors":["Missing transaction id"],"data":[]}',
            ],
        ];
    }
}
