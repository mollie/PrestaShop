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

namespace Mollie\Tests\Unit\Service\ExpressComponent;

use Mollie\Adapter\API\MollieSessionApiAdapterInterface;
use Mollie\Builder\Express\ExpressSessionRequestBuilder;
use Mollie\DTO\Express\ExpressSession;
use Mollie\DTO\Express\ExpressSessionRequest;
use Mollie\DTO\Express\ExpressSubmitCheck;
use Mollie\Logger\PrestaLoggerInterface;
use Mollie\Repository\MolExpressSessionRepositoryInterface;
use Mollie\Repository\OrderRepositoryInterface;
use Mollie\Service\ExpressComponent\ExpressComponentAvailabilityService;
use Mollie\Service\ExpressComponent\ExpressSessionService;
use Mollie\Subscription\Utility\ClockInterface;
use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\HashUtility;

class ExpressSessionServiceTest extends BaseTestCase
{
    const NOW = '2026-10-05 12:00:00';

    /** @var ExpressSessionRequestBuilder|\PHPUnit\Framework\MockObject\MockObject */
    private $requestBuilder;
    /** @var MollieSessionApiAdapterInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionApi;
    /** @var MolExpressSessionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $sessionRepository;
    /** @var OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderRepository;
    /** @var ExpressComponentAvailabilityService|\PHPUnit\Framework\MockObject\MockObject */
    private $availability;
    /** @var ExpressSessionService */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->requestBuilder = $this->mock(ExpressSessionRequestBuilder::class);
        $this->requestBuilder->method('build')->willReturn(
            new ExpressSessionRequest('EUR', '59.90', 'mol_1', [], 'r', 'w', [])
        );
        $this->sessionApi = $this->mock(MollieSessionApiAdapterInterface::class);
        $this->sessionRepository = $this->mock(MolExpressSessionRepositoryInterface::class);
        $this->orderRepository = $this->mock(OrderRepositoryInterface::class);
        $this->orderRepository->method('getOrderIdByCartId')->willReturn(0);
        $this->availability = $this->mock(ExpressComponentAvailabilityService::class);
        $this->availability->method('isAvailable')->willReturn(true);

        $clock = $this->mock(ClockInterface::class);
        $clock->method('getCurrentDate')->willReturn(self::NOW);

        $this->context->method('getCurrencyIso')->willReturn('EUR');
        $this->context->method('getShopId')->willReturn(1);

        $this->cart->id = 7;
        $this->cart->secure_key = 'customer_secure_key';
        $this->cart->method('getOrderTotal')->willReturn(59.9);

        $this->service = new ExpressSessionService(
            $this->requestBuilder,
            $this->sessionApi,
            $this->sessionRepository,
            $this->orderRepository,
            $this->availability,
            $this->context,
            $clock,
            $this->mock(PrestaLoggerInterface::class)
        );
    }

    private function storedSession(float $amount = 59.9, string $createdAt = '2026-10-05 11:50:00', string $currency = 'EUR'): \MolExpressSession
    {
        $row = new \MolExpressSession();
        $row->id_cart = 7;
        $row->session_id = 'sess_stored';
        $row->amount = $amount;
        $row->currency_iso = $currency;
        $row->webhook_token = HashUtility::hash('customer_secure_key');
        $row->status = 'open';
        $row->created_at = $createdAt;

        return $row;
    }

    private function remoteSession(string $id, string $status): ExpressSession
    {
        return new ExpressSession($id, 'token_' . $id, $status, '59.90', 'EUR', self::NOW);
    }

    public function testItReusesAYoungOpenSessionWithTheSameAmount(): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession());
        $this->sessionApi->expects($this->once())->method('get')->with('sess_stored')->willReturn($this->remoteSession('sess_stored', 'open'));
        $this->sessionApi->expects($this->never())->method('create');

        $session = $this->service->getOrCreateSession($this->cart);

        $this->assertSame('sess_stored', $session->getId());
    }

    /**
     * @dataProvider staleStoredSessionProvider
     */
    public function testItCreatesANewSessionWhenTheStoredOneCannotBeReused(\MolExpressSession $stored, ?string $remoteStatus): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn($stored);

        if (null === $remoteStatus) {
            $this->sessionApi->expects($this->never())->method('get');
        } else {
            $this->sessionApi->method('get')->willReturn($this->remoteSession('sess_stored', $remoteStatus));
        }

        $this->sessionApi->expects($this->once())->method('create')->willReturn($this->remoteSession('sess_new', 'open'));
        $this->sessionRepository->expects($this->once())->method('deleteByCartId')->with(7);
        $this->sessionRepository->expects($this->atLeastOnce())->method('persist');

        $session = $this->service->getOrCreateSession($this->cart);

        $this->assertSame('sess_new', $session->getId());
    }

    public function staleStoredSessionProvider(): array
    {
        return [
            'amount changed' => [$this->storedSession(49.9), null],
            'currency changed' => [$this->storedSession(59.9, '2026-10-05 11:50:00', 'USD'), null],
            'too old' => [$this->storedSession(59.9, '2026-10-05 11:40:00'), null],
            'completed at Mollie' => [$this->storedSession(), 'completed'],
            'expired at Mollie' => [$this->storedSession(), 'expired'],
        ];
    }

    public function testItCreatesANewSessionWhenTheStoredOneCannotBeLoaded(): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession());
        $this->sessionApi->method('get')->willThrowException(new \RuntimeException('404'));
        $this->sessionApi->expects($this->once())->method('create')->willReturn($this->remoteSession('sess_new', 'open'));

        $this->assertSame('sess_new', $this->service->getOrCreateSession($this->cart)->getId());
    }

    public function testItPersistsTheNewSessionAgainstTheCart(): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn(null);
        $this->sessionApi->method('create')->willReturn($this->remoteSession('sess_new', 'open'));
        $this->sessionRepository->expects($this->once())->method('persist')->with($this->callback(static function (\MolExpressSession $row) {
            return 7 === $row->id_cart
                && 1 === $row->id_shop
                && 'sess_new' === $row->session_id
                && 59.9 === (float) $row->amount
                && 'EUR' === $row->currency_iso
                && 'open' === $row->status
                && self::NOW === $row->created_at;
        }));

        $this->service->getOrCreateSession($this->cart);
    }

    public function testSubmitIsRejectedWhenTheCartAlreadyHasAnOrder(): void
    {
        $orderRepository = $this->mock(OrderRepositoryInterface::class);
        $orderRepository->method('getOrderIdByCartId')->willReturn(99);
        $clock = $this->mock(ClockInterface::class);
        $clock->method('getCurrentDate')->willReturn(self::NOW);

        $service = new ExpressSessionService(
            $this->requestBuilder,
            $this->sessionApi,
            $this->sessionRepository,
            $orderRepository,
            $this->availability,
            $this->context,
            $clock,
            $this->mock(PrestaLoggerInterface::class)
        );

        $check = $service->validateBeforeSubmit($this->cart);

        $this->assertFalse($check->isOk());
        $this->assertSame(ExpressSubmitCheck::REASON_ORDER_EXISTS, $check->getReason());
    }

    public function testSubmitIsRejectedWhenTheCartIsNoLongerReady(): void
    {
        $availability = $this->mock(ExpressComponentAvailabilityService::class);
        $availability->method('isAvailable')->willReturn(false);
        $clock = $this->mock(ClockInterface::class);
        $clock->method('getCurrentDate')->willReturn(self::NOW);

        $service = new ExpressSessionService(
            $this->requestBuilder,
            $this->sessionApi,
            $this->sessionRepository,
            $this->orderRepository,
            $availability,
            $this->context,
            $clock,
            $this->mock(PrestaLoggerInterface::class)
        );

        $this->assertSame(ExpressSubmitCheck::REASON_CART_NOT_READY, $service->validateBeforeSubmit($this->cart)->getReason());
    }

    public function testSubmitIsAcceptedWhenTheStoredAmountStillMatches(): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession());
        $this->sessionApi->expects($this->never())->method('create');

        $this->assertTrue($this->service->validateBeforeSubmit($this->cart)->isOk());
    }

    public function testASessionBuiltBeforeTheCartChangedOwnerIsNotReused(): void
    {
        $guestSession = $this->storedSession();
        $guestSession->webhook_token = HashUtility::hash('guest_secure_key');
        $this->sessionRepository->method('findLatestByCartId')->willReturn($guestSession);
        $this->sessionApi->expects($this->never())->method('get');
        $this->sessionApi->expects($this->once())->method('create')->willReturn($this->remoteSession('sess_new', 'open'));

        $this->assertSame('sess_new', $this->service->getOrCreateSession($this->cart)->getId());
    }

    public function testSubmitIsRejectedWithAFreshTokenWhenTheCartChangedOwner(): void
    {
        $guestSession = $this->storedSession();
        $guestSession->webhook_token = HashUtility::hash('guest_secure_key');
        $this->sessionRepository->method('findLatestByCartId')->willReturn($guestSession);
        $this->sessionApi->method('create')->willReturn($this->remoteSession('sess_new', 'open'));

        $check = $this->service->validateBeforeSubmit($this->cart);

        $this->assertFalse($check->isOk());
        $this->assertSame(ExpressSubmitCheck::REASON_CART_OWNER_CHANGED, $check->getReason());
        $this->assertSame('token_sess_new', $check->getClientAccessToken());
    }

    public function testANewSessionStoresTheWebhookTokenOfTheCart(): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn(null);
        $this->sessionApi->method('create')->willReturn($this->remoteSession('sess_new', 'open'));
        $this->sessionRepository->expects($this->once())->method('persist')->with($this->callback(static function (\MolExpressSession $row) {
            return HashUtility::hash('customer_secure_key') === $row->webhook_token;
        }));

        $this->service->getOrCreateSession($this->cart);
    }

    public function testSubmitIsRejectedWithAFreshTokenWhenTheAmountChanged(): void
    {
        $this->sessionRepository->method('findLatestByCartId')->willReturn($this->storedSession(49.9));
        $this->sessionApi->method('create')->willReturn($this->remoteSession('sess_new', 'open'));

        $check = $this->service->validateBeforeSubmit($this->cart);

        $this->assertFalse($check->isOk());
        $this->assertSame(ExpressSubmitCheck::REASON_AMOUNT_CHANGED, $check->getReason());
        $this->assertSame('token_sess_new', $check->getClientAccessToken());
        $this->assertSame(['ok' => false, 'reason' => 'amount_changed', 'clientAccessToken' => 'token_sess_new'], $check->jsonSerialize());
    }
}
