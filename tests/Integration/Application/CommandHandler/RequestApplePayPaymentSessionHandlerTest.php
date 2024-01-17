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

use Mollie\Application\Command\RequestApplePayPaymentSession;
use Mollie\Application\CommandHandler\RequestApplePayPaymentSessionHandler;
use Mollie\Tests\Integration\BaseTestCase;
use Mollie\Tests\Mocks\Service\ApiServiceMock;

class RequestApplePayPaymentSessionHandlerTest extends BaseTestCase
{
    /**
     * @dataProvider commandProvider
     */
    public function testHandle(RequestApplePayPaymentSession $command)
    {
        /** @var Mollie $mollie */
        $mollie = Module::getInstanceByName('mollie');
        $apiServiceMock = new ApiServiceMock();
        $handler = new RequestApplePayPaymentSessionHandler($mollie, $apiServiceMock);
        $result = $handler->handle($command);

        $this->assertArrayHasKey('cartId', $result);
        $this->assertArrayHasKey('data', $result);
        $this->assertTrue($result['success']);
        if ($command->getCartId() !== 0) {
            $this->assertEquals($command->getCartId(), $result['cartId']);
        }
    }

    public function commandProvider()
    {
        return [
            'new cart case' => [
                    'command' => new RequestApplePayPaymentSession(
                        'test-validation-url',
                        1,
                        1,
                        0
                    ),
                ],
            'cart exist case' => [
                    'command' => new RequestApplePayPaymentSession(
                        'test-validation-url',
                        1,
                        1,
                        3
                    ),
                ],
        ];
    }
}
