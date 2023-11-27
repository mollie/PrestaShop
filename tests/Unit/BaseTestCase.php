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

namespace Mollie\Tests\Unit;

use Mollie\Adapter\ConfigurationAdapter;
use Mollie\Adapter\Context;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected $backupGlobals = false;

    /** @var \Mollie */
    public $module;
    /** @var ConfigurationAdapter */
    public $configuration;
    /** @var Context */
    public $context;
    /** @var \Customer */
    public $customer;

    protected function setUp(): void
    {
        $this->module = $this->mock(\Mollie::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->context = $this->mock(Context::class);
        $this->customer = $this->mock(\Customer::class);

        parent::setUp();
    }

    public function mock(string $className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
