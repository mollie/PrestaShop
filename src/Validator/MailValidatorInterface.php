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

if (!defined('_PS_VERSION_')) {
    exit;
}

interface MailValidatorInterface
{
    /**
     * @param int $orderStateId
     *
     * @return bool
     */
    public function validate($orderStateId);
}
