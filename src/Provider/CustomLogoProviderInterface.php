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

namespace Mollie\Provider;

use MolPaymentMethod;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface CustomLogoProviderInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getLocalPath();

    /**
     * @return string
     */
    public function getPathUri();

    /**
     * @return string
     */
    public function getLocalLogoPath();

    /**
     * @return string
     */
    public function getLogoPathUri();

    /**
     * @return bool
     */
    public function logoExists();

    /**
     * @return string
     */
    public function getMethodOptionLogo(MolPaymentMethod $methodObj);
}
