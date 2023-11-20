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

namespace Mollie\Validator;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Config\Config;
use Mollie\Service\VoucherService;

if (!defined('_PS_VERSION_')) {
    exit;
}

class VoucherValidator
{
    /**
     * @var ConfigurationAdapter
     */
    private $configuration;

    /**
     * @var VoucherService
     */
    private $voucherService;

    public function __construct(ConfigurationAdapter $configuration, VoucherService $voucherService)
    {
        $this->configuration = $configuration;
        $this->voucherService = $voucherService;
    }

    public function validate(array $products): bool
    {
        if (Config::MOLLIE_VOUCHER_CATEGORY_NULL !== $this->configuration->get(Config::MOLLIE_VOUCHER_CATEGORY)) {
            return true;
        }

        foreach ($products as $product) {
            $voucherCategory = $this->voucherService->getProductCategory($product);

            if ($voucherCategory) {
                return true;
            }
        }

        return false;
    }
}
