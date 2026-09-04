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

namespace Mollie\Tests\Unit\Utility;

use Mollie\Tests\Unit\BaseTestCase;
use Mollie\Utility\LockUtility;

class LockUtilityTest extends BaseTestCase
{
    /**
     * The literal is pinned because the webhook controller has always locked this
     * exact name; changing it lets the webhook and the return flow create the same
     * order twice.
     */
    public function testItNamesTheOrderCreationResourceAfterTheWebhook(): void
    {
        $this->assertSame('webhook-2a4f6c', LockUtility::orderCreation('2a4f6c'));
    }
}
