<?php

namespace Mollie\Core\Payment\Action;

use Mollie\Core\Payment\Exception\CouldNotLockCart;
use Mollie\Exception\MollieException;
use Mollie\Infrastructure\Context\GlobalShopContextInterface;
use Mollie\Infrastructure\EntityManager\EntityManagerInterface;
use Mollie\Infrastructure\EntityManager\ObjectModelUnitOfWork;
use Mollie\Logger\PrestaLoggerInterface;

class LockCartAction
{
    private $logger;
    private $globalShopContext;
    private $entityManager;

    public function __construct(
        PrestaLoggerInterface $logger,
        GlobalShopContextInterface $globalShopContext,
        EntityManagerInterface $entityManager
    ) {
        $this->logger = $logger;
        $this->globalShopContext = $globalShopContext;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws MollieException
     */
    public function run(int $cartId): void
    {
        $this->logger->debug(sprintf('%s - Function called', __METHOD__));

        try {
            $cart = new \MollieCart();

            $cart->id_cart = $cartId;
            $cart->id_shop = $this->globalShopContext->getShopId();

            $this->entityManager->persist($cart, ObjectModelUnitOfWork::UNIT_OF_WORK_SAVE);
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            throw CouldNotLockCart::unknownError($exception);
        }

        $this->logger->debug(sprintf('%s - Function ended', __METHOD__));
    }
}
