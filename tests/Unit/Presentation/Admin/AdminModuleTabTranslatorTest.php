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

namespace Mollie\Tests\Unit\Presentation\Admin;

use Mollie\Presentation\Admin\AdminModuleTabTranslator;
use PHPUnit\Framework\TestCase;

class AdminModuleTabTranslatorTest extends TestCase
{
    public function testItReturnsCorrectTranslation(): void
    {
        $translator = new AdminModuleTabTranslator();
        $this->assertSame('Paramètres', $translator->translate('Settings', 'fr'));
        $this->assertSame('Einstellungen', $translator->translate('Settings', 'de'));
        $this->assertSame('Settings', $translator->translate('Settings', 'en'));
    }

    public function testItFallsBackToEnglish(): void
    {
        $translator = new AdminModuleTabTranslator();
        $this->assertSame('Settings', $translator->translate('Settings', 'xx'));
        $this->assertSame('Mollie', $translator->translate('Mollie', 'fr'));
    }

    public function testItReturnsMissingForUnknownTab(): void
    {
        $translator = new AdminModuleTabTranslator();
        $this->assertSame('Missing', $translator->translate('UnknownTab', 'en'));
        $this->assertSame('Missing', $translator->translate('UnknownTab', 'fr'));
    }

    public function testItHandlesEmptyTabName(): void
    {
        $translator = new AdminModuleTabTranslator();
        $this->assertSame('Missing', $translator->translate('', 'en'));
    }
}
