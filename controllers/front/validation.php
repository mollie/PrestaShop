<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */
declare(strict_types=1);

use Mollie\Repository\PaymentMethodRepositoryInterface;
use Mollie\Utility\ExceptionUtility;
use Mollie\Logger\LoggerInterface;

class MollieValidationModuleFrontController extends ModuleFrontController
{
    private const FILE_NAME = 'validation';

    public function __construct()
    {
        parent::__construct();
    }

    public function postProcess(): void
    {
        parent::postProcess();

        /** @var PaymentMethodRepositoryInterface $paymentMethodRepository */
        $paymentMethodRepository = $this->module->getService(PaymentMethodRepositoryInterface::class);

        /** @var LoggerInterface $logger */
        $logger = $this->module->getService(LoggerInterface::class);

        $logger->debug(sprintf('%s - controller called', self::FILE_NAME));

        try {
            $paymentMethodRepository->flagOldPaymentRecordsByCartId($this->context->cart->id);
        } catch (Exception $e) {
            $logger->error(sprintf('%s - Error while flagging old payment records', self::FILE_NAME), [
                'exceptions' => ExceptionUtility::getExceptions($e)
            ]);
        }
    }
} 