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

namespace Mollie\Adapter\API;

use Mollie\DTO\Express\ExpressSession;
use Mollie\DTO\Express\ExpressSessionRequest;

if (!defined('_PS_VERSION_')) {
    exit;
}

interface MollieSessionApiAdapterInterface
{
    public function create(ExpressSessionRequest $request): ExpressSession;

    public function get(string $sessionId): ExpressSession;
}
