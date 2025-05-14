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
use Mollie\Service\LanguageService;
use PHPUnit\Framework\TestCase;

class BaseTestCase extends TestCase
{
    protected $backupGlobals = false;

    /** @var \Cart */
    public $cart;

    /** @var ConfigurationAdapter */
    public $configuration;

    /** @var Context */
    public $context;

    /** @var LanguageService */
    public $languageService;

    protected function setUp(): void
    {
        $this->cart = $this->mock(\Cart::class);
        $this->configuration = $this->mock(ConfigurationAdapter::class);
        $this->context = $this->mock(Context::class);
        $this->languageService = $this->mock(LanguageService::class);

        parent::setUp();
    }

    public function mock(string $className)
    {
        return $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
