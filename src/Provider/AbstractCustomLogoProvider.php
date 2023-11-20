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

if (!defined('_PS_VERSION_')) {
    exit;
}

abstract class AbstractCustomLogoProvider implements CustomLogoProviderInterface
{
    /**
     * @return string
     */
    public function getLocalLogoPath()
    {
        return "{$this->getLocalPath()}views/img/customLogo/{$this->getName()}.jpg";
    }

    /**
     * @return string
     */
    public function getLogoPathUri()
    {
        return "{$this->getPathUri()}views/img/customLogo/{$this->getName()}.jpg";
    }

    /**
     * @return bool
     */
    public function logoExists()
    {
        return file_exists($this->getLocalLogoPath());
    }
}
