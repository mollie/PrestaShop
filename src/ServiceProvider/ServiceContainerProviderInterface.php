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

declare(strict_types=1);

namespace Mollie\ServiceProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface ServiceContainerProviderInterface
{
    /**
     * Gets service that is defined by module container.
     */
    public function getService(string $serviceName);

    /**
     * Extending the service. Useful for tests to dynamically change the implementations
     *
     * @param ?string $concrete - a class name
     *
     * @return mixed
     */
    public function extend(string $id, ?string $concrete = null);
}
