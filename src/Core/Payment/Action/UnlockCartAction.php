<?php

namespace Mollie\Core\Payment\Action;

use Mollie\Core\Payment\Exception\CouldNotUnlockCart;
use Mollie\Core\Shared\Repository\MollieCartRepositoryInterface;
use Mollie\Exception\MollieException;
use Mollie\Infrastructure\Context\GlobalShopContextInterface;
use Mollie\Infrastructure\EntityManager\EntityManagerInterface;
use Mollie\Infrastructure\EntityManager\ObjectModelUnitOfWork;
use Mollie\Logger\PrestaLoggerInterface;

class UnlockCartAction
{
    private $logger;
    private $globalShopContext;
    private $entityManager;
    private $mollieCartRepository;

    public function __construct(
        PrestaLoggerInterface $logger,
        GlobalShopContextInterface $globalShopContext,
        EntityManagerInterface $entityManager,
        MollieCartRepositoryInterface $mollieCartRepository
    ) {
        $this->logger = $logger;
        $this->globalShopContext = $globalShopContext;
        $this->entityManager = $entityManager;
        $this->mollieCartRepository = $mollieCartRepository;
    }

    /**
     * @throws MollieException
     */
    public function run(int $cartId): void
    {
        $this->logger->debug(sprintf('%s - Function called', __METHOD__));

        try {
            $mollieCart = $this->mollieCartRepository->findOneBy([
                'id_cart' => $cartId,
                'id_shop' => $this->globalShopContext->getShopId(),
            ]);

            if (!$mollieCart) {
                $this->logger->debug(sprintf('%s - Function ended', __METHOD__));

                return;
            }

            $this->entityManager->persist($mollieCart, ObjectModelUnitOfWork::UNIT_OF_WORK_DELETE);
            $this->entityManager->flush();
        } catch (\Throwable $exception) {
            throw CouldNotUnlockCart::unknownError($exception);
        }

        $this->logger->debug(sprintf('%s - Function ended', __METHOD__));
    }
}
